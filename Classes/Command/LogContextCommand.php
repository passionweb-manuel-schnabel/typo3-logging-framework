<?php

declare(strict_types=1);

namespace Passionweb\LoggingApi\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\Channel;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\Writer\FileWriter;

final class LogContextCommand extends Command
{
    public function __construct(
        // Same channel as last week: DatabaseWriter, RotatingFileWriter, PhpErrorLogWriter.
        #[Channel('codebreak')]
        private readonly LoggerInterface $logger,
        private readonly ConnectionPool $connectionPool,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $run = uniqid();

        $io->section('Placeholders');
        // Never concatenate values into the message. The message stays the
        // same for every record, the values travel in the context array.
        $this->logger->info('Record {table}:{uid} saved, tags {tags}', [
            'table' => 'tt_content',
            'uid' => 42,
            // Arrays are not interpolated, {tags} stays in the message.
            'tags' => ['news', 'codebreak'],
            'run' => $run,
        ]);

        $io->section('Exceptions');
        try {
            throw new \RuntimeException('Codebreak exception', 1760000002);
        } catch (\RuntimeException $exception) {
            // The key "exception" is special: the FileWriter appends class,
            // message, file and line to the message.
            $this->logger->error('Import of {file} failed', [
                'file' => 'news.xml',
                'exception' => $exception,
                'run' => $run,
            ]);
        }

        $io->section('FileWriter: interpolated');
        $io->writeln($this->getFileLines($run));

        $io->section('DatabaseWriter: raw');
        $rows = $this->connectionPool->getQueryBuilderForTable('sys_log')
            ->select('message', 'data')
            ->from('sys_log')
            ->where('data LIKE :run')
            ->setParameter('run', '%' . $run . '%')
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();
        foreach ($rows as $row) {
            // The placeholders are kept, the context is stored as JSON.
            $io->writeln($row['message']);
            $io->writeln(mb_strimwidth($row['data'], 0, 120, '...'));
        }

        $io->success('The file interpolates {placeholders}, the database keeps them');

        return Command::SUCCESS;
    }

    private function getFileLines(string $run): array
    {
        /** @var Logger $logger */
        $logger = $this->logger;
        foreach ($logger->getWriters()['info'] as $writer) {
            if ($writer instanceof FileWriter) {
                $lines = file($writer->getLogFile(), FILE_IGNORE_NEW_LINES) ?: [];
                $lines = array_filter($lines, static fn(string $line): bool => str_contains($line, $run));
                return array_map(static fn(string $line): string => mb_strimwidth($line, 0, 200, '...'), $lines);
            }
        }
        return [];
    }
}
