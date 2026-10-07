<?php

declare(strict_types=1);

namespace Passionweb\LoggingApi\Command;

use Passionweb\LoggingApi\Service\LegacyImportService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Log\LogManager;

final class LogComponentsCommand extends Command
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LogManager $logManager,
        private readonly LegacyImportService $legacyImportService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Constructor injection');
        // The logger name is the "component" in every log line.
        $io->writeln('logger name: ' . $this->logger->getName());

        $io->section('LogManager::getLogger(self::class)');
        // Backslashes become dots. The same name returns the same logger instance.
        $logger = $this->logManager->getLogger(self::class);
        $io->writeln('logger name: ' . $logger->getName());
        $io->writeln('same instance as the injected one: ' . var_export($logger === $this->logger, true));

        $io->section('LogManager::getLogger(\'tx_codebreak_legacy\')');
        // Underscores become dots as well. The dots are the path that is used
        // to look up the configuration in $GLOBALS['TYPO3_CONF_VARS']['LOG'].
        $io->writeln('logger name: ' . $this->logManager->getLogger('tx_codebreak_legacy')->getName());

        $io->section('LoggerAwareInterface');
        $this->legacyImportService->import();
        $io->writeln('logger available in the constructor: ' . var_export($this->legacyImportService->hadLoggerInConstructor(), true));

        $io->success('Component names are class names, with dots instead of backslashes and underscores');

        return Command::SUCCESS;
    }
}
