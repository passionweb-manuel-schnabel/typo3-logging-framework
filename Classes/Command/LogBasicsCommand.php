<?php

declare(strict_types=1);

namespace Passionweb\LoggingApi\Command;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Environment;

final class LogBasicsCommand extends Command
{
    // The eight PSR-3 levels, from the most to the least severe.
    private const LEVELS = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG,
    ];

    public function __construct(
        // Requesting a LoggerInterface in the constructor is all it takes.
        // TYPO3 asks the LogManager for a logger named after this class.
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $logFile = $this->findLogFile();
        $linesBefore = $this->countLines($logFile);

        $io->section('Logging one message per level');
        foreach (self::LEVELS as $level) {
            // $this->logger->warning('...') is a shortcut for log(LogLevel::WARNING, '...')
            $this->logger->log($level, 'Codebreak says hello with level ' . $level);
            $io->writeln('logged: ' . $level);
        }

        $io->section('What reached ' . str_replace(Environment::getProjectPath() . '/', '', $logFile));
        $newLines = array_slice(file($logFile, FILE_IGNORE_NEW_LINES) ?: [], $linesBefore);
        foreach ($newLines as $line) {
            $io->writeln($line);
        }

        // The core default is a single FileWriter starting at WARNING, so
        // debug(), info() and notice() never reach the log file.
        $io->success(count(self::LEVELS) . ' messages logged, ' . count($newLines) . ' written');

        return Command::SUCCESS;
    }

    private function findLogFile(): string
    {
        // The default file is var/log/typo3_<hash>.log, the hash makes the name hard to guess.
        foreach (glob(Environment::getVarPath() . '/log/typo3_*.log') ?: [] as $file) {
            if (!str_contains($file, 'deprecations')) {
                return $file;
            }
        }
        throw new \RuntimeException('No TYPO3 log file found in var/log/', 1760000001);
    }

    private function countLines(string $file): int
    {
        return count(file($file) ?: []);
    }
}
