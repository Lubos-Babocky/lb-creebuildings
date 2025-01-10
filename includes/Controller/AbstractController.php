<?php

namespace LB\CreeBuildings\Controller;

use LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\Service\LogService;

/**
 * Description of AbstractController
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
abstract class AbstractController
{

    const DEFAULT_PAGE = 'lb-creebuildings';
    const DEFAULT_CAPABILITY = 'manage_options';
    const ACTION_IDENTIFIER = 'Action';

    protected array $arguments;
    protected string $templateFilePath;
    protected ConfigurationService $configurationService;

    abstract protected function injectDependencies(): void;

    public function __construct(
        protected readonly string $defaultAction,
        protected readonly string $pluginName,
        protected readonly string $pluginIcon,
        protected readonly int $pluginPosition
    )
    {
        $this->initController();
    }

    private function initController(): void
    {
        if (empty($getParams = filter_input_array(INPUT_GET))) {
            return;
        }
        $this->handleArguments();
        $this->configurationService = ConfigurationService::GetInstance();
        $this->injectDependencies();
    }

    private function handleArguments(): void
    {
        if (empty($getParams = filter_input_array(INPUT_GET))) {
            return;
        }
        $this->arguments = $getParams;
        if (!array_key_exists('page', $this->arguments) || empty($this->arguments['page']) || $this->arguments['page'] === static::DEFAULT_PAGE) {
            $this->arguments['action'] = $this->defaultAction;
        } else {
            $this->arguments['action'] = str_replace(sprintf('%s-', static::DEFAULT_PAGE), '', $this->arguments['page']);
        }
    }

    private function getRealActionName(string $actionName): string
    {
        if (str_ends_with($actionName, 'Action')) {
            $actionName = substr($actionName, 0, -strlen('Action'));
        }
        return $actionName;
    }

    private function getFullActionName(string $actionName = null): string
    {
        $actionName = $actionName ?? $this->arguments['action'];
        if (str_ends_with($actionName, 'Action')) {
            $actionName = substr($actionName, 0, -strlen('Action'));
        }
        $fullActionName = sprintf('%sAction', $actionName);
        if (!method_exists($this, $fullActionName)) {
            throw new \Exception(sprintf("Action %s does not exist in %s", $fullActionName, static::class));
        }
        return $fullActionName;
    }

    private function resolveView(): void
    {
        if (!is_readable($templatePath = str_replace('includes/Controller/', '', plugin_dir_path(__FILE__)) . "assets/templates/admin-page/{$this->arguments['action']}.php")) {
            throw new \Exception(sprintf('Template file for action %s not found', $templatePath));
        } else {
            include_once $templatePath;
        }
    }

    public function serve(): void
    {
        try {
            call_user_func([$this, $this->getFullActionName()]);
            $this->resolveView();
        } catch (\Exception $ex) {
            LogService::GetInstance()->handleException($ex);
        }
    }

    public function buildActionUri(string $actionName, array $arguments): string
    {
        $uriParams = [
            'page' => ($actionName === $this->defaultAction)
            ? static::DEFAULT_PAGE
            : sprintf('%s-%s', static::DEFAULT_PAGE, $this->getRealActionName($actionName))
        ];
        return add_query_arg(
            array_merge($uriParams, $arguments),
            admin_url('admin.php')
        );
    }

    protected function setArgument(string $argumentName, mixed $value): void
    {
        $this->arguments[$argumentName] = $value;
    }

    public function getArgument(string $argumentName, mixed $defaultValue = null): mixed
    {
        return array_key_exists($argumentName, $this->arguments) && !empty($this->arguments[$argumentName])
            ? $this->arguments[$argumentName]
            : $defaultValue;
    }

    protected function redirect(string $actionName, array $arguments = []): void
    {
        ob_end_clean();
        wp_redirect($this->buildActionUri($actionName, $arguments));
        die;
    }

    public function registerWordpressAdminPages(): void
    {
        $this->registerAdminPage();
        $this->registerAdminSubpages();
    }

    private function registerAdminPage(): void
    {
        add_menu_page(
            $this->pluginName,
            $this->pluginName,
            static::DEFAULT_CAPABILITY,
            static::DEFAULT_PAGE,
            [$this, 'serve'],
            $this->pluginIcon,
            $this->pluginPosition
        );
    }

    private function registerAdminSubpages(): void
    {
        /** @var \ReflectionMethod $method */
        foreach ((new \ReflectionClass($this))->getMethods() as $method) {
            $actionName = $this->removeActionSuffix($method->name);
            if (empty($actionName) || substr($method->name, -strlen(static::ACTION_IDENTIFIER)) !== static::ACTION_IDENTIFIER) {
                continue; //[L:] Not an action!
            }
            if ($actionName === $this->defaultAction) {
                continue; //[L:] Default action already defined in registerMainAdminPage()
            }
            $matches = [];
            preg_match('/@menuTitle\s+(.+)/', $method->getDocComment(), $matches);
            add_submenu_page(
                empty($matches)
                    ? ''
                    : static::DEFAULT_PAGE,
                empty($matches)
                    ? $actionName
                    : ucfirst($matches[1]),
                empty($matches)
                    ? $actionName
                    : ucfirst($matches[1]),
                static::DEFAULT_CAPABILITY,
                sprintf('%s-%s', static::DEFAULT_PAGE, lcfirst($actionName)),
                [$this, 'serve']
            );
        }
    }

    public function registerAdminStyles($hookSuffix): void
    {
        if (strpos($hookSuffix, static::DEFAULT_PAGE) !== false) {
            wp_enqueue_style('lb-creebuildings-admin-styles', $this->configurationService->getPluginRootFolder() . '/assets/css/admin-style.css');
        }
    }

    protected function removeActionSuffix(string $actionName): string
    {
        return preg_replace(sprintf('/%s$/', static::ACTION_IDENTIFIER), '', $actionName);
    }

    public final static function RegisterPlugin(
        string $defaultAction,
        string $pluginName,
        string $pluginIcon,
        int $pluginPosition
    ): void
    {
        ob_start();
        $instance = new static($defaultAction, $pluginName, $pluginIcon, $pluginPosition);
        add_action('admin_menu', [$instance, 'registerWordpressAdminPages']);
        // add_action('admin_enqueue_scripts', [$instance, 'registerAdminStyles']);
    }
}
