<?php

namespace LB\CreeBuildings\Service;

/**
 * Description of LogService
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class LogService extends AbstractService {

    protected ConfigurationService $configurationService;
    protected string $accessLogFilePath;
    protected string $errorLogFilePath;

    protected function injectDependencies(): void {
        $this->configurationService = ConfigurationService::GetInstance();
        $this->initLogFilesPaths();
    }

    public function handleException(\Exception $ex): void {
        $this->writeErrorLog($ex->getMessage());
        echo sprintf('<h3>Exception message: %s</h3><pre>', $ex->getMessage());
        var_dump($ex);
        echo '</pre>';
    }

    public function writeAccessLog(string $message) {
        try {
            $log = fopen($this->accessLogFilePath, 'a');
            fwrite($log, sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message));
        } finally {
            fclose($log);
        }
    }

    public function writeErrorLog(string $message): void {
        try {
            $log = fopen($this->errorLogFilePath, 'a');
            fwrite($log, sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message));
        } finally {
            fclose($log);
        }
    }

    private function initLogFilesPaths(): void {
        $logFolder = sprintf(
                '%s/%s',
                rtrim($this->configurationService->getPluginRootFolder(), '/'),
                ltrim($this->configurationService->getConfig('LOG_FOLDER_PATH'), '/')
        );
        if(!is_dir($logFolder)) {
            mkdir($logFolder);
        }
        $errorLogFile = sprintf('%s/%s', rtrim($logFolder), ltrim($this->configurationService->getConfig('LOG_FILE_ERROR_PATH')));
        if(!is_writable($errorLogFile)) {
            touch($errorLogFile);
        }
        $this->errorLogFilePath = $errorLogFile;
        $accessLogFile = sprintf('%s/%s', rtrim($logFolder), ltrim($this->configurationService->getConfig('LOG_FILE_ACCESS_PATH')));
        if(!is_writable($accessLogFile)) {
            touch($accessLogFile);
        }
        $this->accessLogFilePath = $accessLogFile;
    }
}
