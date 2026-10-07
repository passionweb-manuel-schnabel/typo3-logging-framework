<?php

declare(strict_types=1);

namespace Passionweb\LoggingApi\Service;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// The older way to get a logger: implement LoggerAwareInterface and use the
// trait. TYPO3 calls setLogger() right after the object has been created.
final class LegacyImportService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private bool $hadLoggerInConstructor;

    public function __construct()
    {
        // setLogger() has not been called yet, $this->logger is still null here.
        $this->hadLoggerInConstructor = $this->logger !== null;
    }

    public function hadLoggerInConstructor(): bool
    {
        return $this->hadLoggerInConstructor;
    }

    public function import(): void
    {
        $this->logger?->warning('Legacy import finished');
    }
}
