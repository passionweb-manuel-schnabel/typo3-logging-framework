<?php

declare(strict_types=1);

namespace Passionweb\LoggingApi\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\Channel;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\Writer\FileWriter;

final class LogWritersCommand extends Command
{
    public function __construct(
        #[Channel('codebreak')]
        private readonly LoggerInterface $logger,
        private readonly ConnectionPool $connectionPool,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Writers of the "codebreak" channel');
        /** @var Logger $logger */
        $logger = $this->logger;
        // The disabled SyslogWriter is not instantiated at all.
        foreach ($logger->getWriters()['info'] as $writer) {
            $io->writeln((new \ReflectionClass($writer))->getShortName());
        }

        $io->section('Logging one message');
        $message = 'Codebreak ' . uniqid();
        // The PhpErrorLogWriter prints this line to STDERR right away.
        $this->logger->info($message);

        $io->section('RotatingFileWriter');
        foreach ($logger->getWriters()['info'] as $writer) {
            if ($writer instanceof FileWriter) {
                $io->writeln(str_replace(Environment::getProjectPath() . '/', '', $writer->getLogFile()));
                $lines = file($writer->getLogFile(), FILE_IGNORE_NEW_LINES) ?: [];
                $io->writeln((string)end($lines));
            }
        }

        $io->section('DatabaseWriter');
        $row = $this->connectionPool->getConnectionForTable('sys_log')
            ->select(['component', 'level', 'message', 'request_id'], 'sys_log', ['message' => $message])
            ->fetchAssociative();
        // The level is stored as a number: 0 = emergency ... 7 = debug.
        $io->table(array_keys($row), [$row]);

        $io->success('Same record, three writers, three places');

        return Command::SUCCESS;
    }
}
