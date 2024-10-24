<?php

namespace LB\CreeBuildings\Controller;

use LB\CreeBuildings\Service\ConfigurationService,
    LB\CreeBuildings\Service\LogService;

/**
 * Description of AbstractController
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
abstract class AbstractController {

    const DefaultPage = 'lb-creebuildings';
    const DefaultCapability = 'manage_options';
    const ActionIdentifier = 'Action';

    protected array $arguments;
    protected string $templateFilePath;
    protected ConfigurationService $configurationService;

    abstract protected function injectDependencies(): void;

    public function __construct(
            protected readonly string $defaultAction,
            protected readonly string $pluginName,
            protected readonly string $pluginIcon,
            protected readonly int $pluginPosition
    ) {
        $this->configurationService = ConfigurationService::GetInstance();
        $this->injectDependencies();
        $this->handleArguments();
    }

    private function handleArguments(): void {
        $this->arguments = filter_input_array(INPUT_GET);
        if (!array_key_exists('page', $this->arguments) || empty($this->arguments['page']) || $this->arguments['page'] === static::DefaultPage) {
            $this->arguments['action'] = $this->defaultAction;
        } else {
            $this->arguments['action'] = str_replace(sprintf('%s-', static::DefaultPage), '', $this->arguments['page']);
        }
    }

    private function getFullActionName(string $actionName = null): string {
        $fullActionName = sprintf('%sAction', rtrim($actionName ?? $this->arguments['action'], 'Action'));
        if (!method_exists($this, $fullActionName)) {
            throw new \Exception(sprintf("Action %s does not exist in %s", $fullActionName, static::class));
        }
        return $fullActionName;
    }

    private function resolveView(): void {
        if (!is_readable($templatePath = str_replace('includes/Controller/', '', plugin_dir_path(__FILE__)) . "templates/admin-page/{$this->arguments['action']}.php")) {
            throw new \Exception(sprintf('Template file for action %s not found', $templatePath));
        } else {
            include_once $templatePath;
        }
    }

    public function serve(): void {
        try {
            call_user_func([$this, $this->getFullActionName()]);
            $this->resolveView();
        } catch (\Exception $ex) {
            LogService::GetInstance()->handleException($ex);
        }
    }

    public function buildActionUri(string $actionName, array $arguments): string {
        $uriParams = [
            'page' => ($actionName === $this->defaultAction) ? static::DefaultPage : sprintf('%s-%s', static::DefaultPage, lcfirst(rtrim($actionName, 'Action')))
        ];
        return add_query_arg(
                array_merge($uriParams, $arguments),
                admin_url('admin.php')
        );
    }

    protected function setArgument(string $argumentName, mixed $value): void {
        $this->arguments[$argumentName] = $value;
    }

    public function getArgument(string $argumentName, mixed $defaultValue = null): mixed {
        return array_key_exists($argumentName, $this->arguments) && !empty($this->arguments[$argumentName]) ? $this->arguments[$argumentName] : $defaultValue;
    }

    protected function redirect(string $actionName, array $arguments = []): void {
        ob_end_clean();
        wp_redirect($this->buildActionUri($actionName, $arguments));
        die;
    }

    public function registerWordpressAdminPages(): void {
        $this->registerAdminPage();
        $this->registerAdminSubpages();
    }

    private function registerAdminPage(): void {
        add_menu_page(
                $this->pluginName,
                $this->pluginName,
                static::DefaultCapability,
                static::DefaultPage,
                [$this, 'serve'],
                $this->pluginIcon,
                $this->pluginPosition
        );
    }

    private function registerAdminSubpages(): void {
        /** @var \ReflectionMethod $method */
        foreach ((new \ReflectionClass($this))->getMethods() as $method) {
            $actionName = $this->removeActionSuffix($method->name);
            if (empty($actionName) || substr($method->name, -strlen(static::ActionIdentifier)) !== static::ActionIdentifier) {
                continue; //[L:] Not an action!
            }
            if ($actionName === $this->defaultAction) {
                continue; //[L:] Default action already defined in registerMainAdminPage()
            }
            $matches = [];
            preg_match('/@menuTitle\s+(\w+)/', $method->getDocComment(), $matches);
            add_submenu_page(
                    empty($matches) ? '' : static::DefaultPage,
                    empty($matches) ? $actionName : ucfirst($matches[1]),
                    empty($matches) ? $actionName : ucfirst($matches[1]),
                    static::DefaultCapability,
                    sprintf('%s-%s', static::DefaultPage, lcfirst($actionName)),
                    [$this, 'serve']
            );
        }
    }

    public function registerAdminStyles($hookSuffix): void {
        if (strpos($hookSuffix, static::DefaultPage) !== false) {
            wp_enqueue_style('lb-creebuildings-admin-styles', $this->configurationService->getPluginRootFolder() . '/assets/css/admin-style.css');
        }
    }

    protected function removeActionSuffix(string $actionName): string {
        return preg_replace(sprintf('/%s$/', static::ActionIdentifier), '', $actionName);
    }

    public final static function RegisterPlugin(
            string $defaultAction,
            string $pluginName,
            string $pluginIcon,
            int $pluginPosition
    ): void {
        ob_start();
        $instance = new static($defaultAction, $pluginName, $pluginIcon, $pluginPosition);
        add_action('admin_menu', [$instance, 'registerWordpressAdminPages']);
       // add_action('admin_enqueue_scripts', [$instance, 'registerAdminStyles']);
    }
}
