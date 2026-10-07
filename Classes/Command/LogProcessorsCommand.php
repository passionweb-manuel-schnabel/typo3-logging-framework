<?php

declare(strict_types=1);

namespace Passionweb\LoggingApi\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Log\Channel;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\Writer\FileWriter;

final class LogProcessorsCommand extends Command
{
    public function __construct(
        #[Channel('processors')]
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $run = uniqid();

        $this->logger->debug('Cache warmed up', ['run' => $run]);
        $this->logger->error('Cache warmup failed', ['run' => $run]);

        /** @var Logger $logger */
        $logger = $this->logger;
        $writer = $logger->getWriters()['debug'][0];
        assert($writer instanceof FileWriter);
        $lines = file($writer->getLogFile(), FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($lines as $line) {
            if (!str_contains($line, $run)) {
                continue;
            }
            // Everything after " - " is the JSON encoded data of the record.
            [$message, $json] = explode(' - ', $line, 2);
            $io->section(substr($message, strpos($message, '[')));
            foreach (json_decode($json, true) as $key => $value) {
                $io->writeln(str_pad($key, 12) . ': ' . (string)$value);
            }
        }

        // No HTTP request on the CLI, so the WebProcessor silently adds nothing.
        // Look closely at the introspection data: class and function point to
        // the method that logged, file and line to the place that called it.
        $io->success('class + function = who logged, file + line = who called it');

        return Command::SUCCESS;
    }
}
