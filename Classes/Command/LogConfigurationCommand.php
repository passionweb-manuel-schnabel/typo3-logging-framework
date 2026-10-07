<?php

declare(strict_types=1);

namespace Passionweb\LoggingApi\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Log\Writer\FileWriter;

final class LogConfigurationCommand extends Command
{
    // Three loggers, three different places in $GLOBALS['TYPO3_CONF_VARS']['LOG'].
    private const LOGGER_NAMES = [
        // Matches LOG.Passionweb.LoggingApi from ext_localconf.php
        self::class,
        // Matches LOG.TYPO3.CMS.Core.Resource.ResourceStorage of the core
        'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
        // Matches nothing, falls back to the global LOG.writerConfiguration
        'Vendor\\SomeExtension\\Service\\SomeService',
    ];

    public function __construct(
        private readonly LogManager $logManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach (self::LOGGER_NAMES as $loggerName) {
            /** @var Logger $logger */
            $logger = $this->logManager->getLogger($loggerName);
            $io->section($logger->getName());

            // getWriters() is keyed by level and lists every writer active on that level.
            $rows = [];
            foreach ($logger->getWriters() as $level => $writers) {
                $rows[] = [$level, implode(', ', array_map($this->describeWriter(...), $writers))];
            }
            $io->table(['level', 'writers'], $rows);
        }

        $this->logManager->getLogger(self::class)->debug('This debug message is written now');

        $io->success('The most specific configuration wins and replaces everything above it');

        return Command::SUCCESS;
    }

    private function describeWriter(object $writer): string
    {
        $description = (new \ReflectionClass($writer))->getShortName();
        if ($writer instanceof FileWriter) {
            $description .= ' (' . str_replace(Environment::getProjectPath() . '/', '', $writer->getLogFile()) . ')';
        }
        return $description;
    }
}
