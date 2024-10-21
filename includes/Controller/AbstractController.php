<?php

namespace LB\CreeBuildings\Controller;

use LB\CreeBuildings\Service\LogService;

/**
 * Description of AbstractController
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
abstract class AbstractController {

    const DefaultAction = 'index';
    const DefaultTemplate = 'admin-page';
    const DefaultPage = 'lb-creebuildings';

    protected array $arguments;

    protected string $templateFilePath;

    abstract protected function injectDependencies(): void;

    public function __construct() {
        $this->injectDependencies();
        $this->handleArguments();
    }

    private function handleArguments(): void {
        $this->arguments = filter_input_array(INPUT_GET);
        if (!array_key_exists('action', $this->arguments) || empty($this->arguments['action'])) {
            $this->arguments['action'] = static::DefaultAction;
        }
    }

    private function getFullActionName(string $actionName = null): string {
        $fullActionName = sprintf('%sAction', rtrim($actionName ?? $this->arguments['action'], 'Action'));
        if(!method_exists($this, $fullActionName)) {
            throw new \Exception(sprintf("Action %s does not exist in %s", $fullActionName, static::class));
        }
        return $fullActionName;
    }

    private function resolveView(): void {
        if(!is_readable($templatePath = str_replace('includes/Controller/', '', plugin_dir_path(__FILE__))."templates/admin-page/{$this->arguments['action']}.php")) {
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

    public function buildActionUri(string $actionName, array $argumets): string {
        $uriParams = [
            'page' => static::DefaultPage,
            'action' => rtrim($actionName, 'Action')
        ];
        return add_query_arg(
                array_merge($uriParams, $argumets),
                admin_url('admin.php')
        );
    }

    public function getArgument(string $argumentName, mixed $defaultValue = null): mixed {
        return array_key_exists($argumentName, $this->arguments) && !empty($this->arguments[$argumentName]) ? $this->arguments[$argumentName] : $defaultValue;
    }

    protected function redirectToAction(string $actionName): void {
        $this->arguments['action'] = rtrim($actionName);
    }
}
