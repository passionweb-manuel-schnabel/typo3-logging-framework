<?php

declare(strict_types=1);

namespace Passionweb\LoggingApi\Log\Writer;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;

// Writes one JSON object per line, ready for tools like jq, Loki or Elasticsearch.
final class JsonLinesWriter extends AbstractWriter
{
    private string $logFile = '';

    // The constructor of AbstractWriter turns every option into a setter call:
    // 'logFile' => '...' calls setLogFile('...'). An option without a setter
    // throws an InvalidLogWriterConfigurationException.
    public function setLogFile(string $logFile): void
    {
        $this->logFile = Environment::getVarPath() . '/log/' . basename($logFile);
    }

    public function getLogFile(): string
    {
        return $this->logFile;
    }

    public function writeLog(LogRecord $record): self
    {
        if ($this->logFile === '') {
            return $this;
        }
        $data = $record->getData();
        if (($data['exception'] ?? null) instanceof \Throwable) {
            $data['exception'] = $data['exception']->getMessage();
        }
        $line = json_encode([
            'time' => date('c', (int)$record->getCreated()),
            'level' => $record->getLevel(),
            'component' => $record->getComponent(),
            'request' => $record->getRequestId(),
            // interpolate() is inherited and replaces the {placeholders}.
            'message' => $this->interpolate($record->getMessage(), $data),
            'data' => $data,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->logFile, $line . LF, FILE_APPEND | LOCK_EX);

        return $this;
    }
}
