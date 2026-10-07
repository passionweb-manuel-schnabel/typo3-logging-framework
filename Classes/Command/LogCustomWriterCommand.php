<?php

declare(strict_types=1);

namespace Passionweb\LoggingApi\Command;

use Passionweb\LoggingApi\Log\Writer\JsonLinesWriter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Log\Channel;
use TYPO3\CMS\Core\Log\Exception\InvalidLogWriterConfigurationException;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;

final class LogCustomWriterCommand extends Command
{
    public function __construct(
        #[Channel('json')]
        private readonly LoggerInterface $logger,
        private readonly LogManager $logManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Logging through the JsonLinesWriter');
        $this->logger->notice('Page {uid} published', ['uid' => 12]);
        /** @var Logger $logger */
        $logger = $this->logger;
        $writer = $logger->getWriters()['notice'][0];
        assert($writer instanceof JsonLinesWriter);
        $lines = file($writer->getLogFile(), FILE_IGNORE_NEW_LINES) ?: [];
        $io->writeln(json_encode(json_decode((string)end($lines)), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $io->section('An option without a setter');
        try {
            new JsonLinesWriter(['path' => 'codebreak.jsonl']);
        } catch (InvalidLogWriterConfigurationException $exception) {
            $io->writeln($exception->getMessage());
        }

        $io->section('The same typo in $GLOBALS[\'TYPO3_CONF_VARS\'][\'LOG\']');
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['jsontypo']['writerConfiguration'] = [
            'debug' => [JsonLinesWriter::class => ['path' => 'codebreak.jsonl']],
        ];
        // The LogManager catches the exception, drops the writer and only logs
        // a warning to a logger that now has no writer at all.
        /** @var Logger $brokenLogger */
        $brokenLogger = $this->logManager->getLogger('jsontypo');
        $io->writeln('writers of "jsontypo": ' . count($brokenLogger->getWriters()));

        $io->success('A typo in a writer option does not crash, the writer is just gone');

        return Command::SUCCESS;
    }
}
