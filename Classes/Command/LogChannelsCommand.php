<?php

declare(strict_types=1);

namespace Passionweb\LoggingApi\Command;

use Passionweb\LoggingApi\Service\LoginAuditService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Log\Channel;
use TYPO3\CMS\Core\Log\LogManager;

final class LogChannelsCommand extends Command
{
    public function __construct(
        private readonly LoginAuditService $loginAuditService,
        // #[Channel] on a single parameter: only this logger uses the channel.
        #[Channel('security')]
        private readonly LoggerInterface $securityLogger,
        private readonly LogManager $logManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Two classes, one channel');
        $io->writeln('LoginAuditService logger: ' . $this->loginAuditService->getLogger()->getName());
        $io->writeln('LogChannelsCommand logger: ' . $this->securityLogger->getName());
        $io->writeln('same instance: ' . var_export($this->loginAuditService->getLogger() === $this->securityLogger, true));

        $this->loginAuditService->failedLogin('editor');
        $this->securityLogger->info('Password of "editor" changed');

        $io->section('The attribute is read by dependency injection only');
        // getLogger(self::class) knows nothing about #[Channel], it uses the class name.
        $io->writeln('LogManager::getLogger(LoginAuditService::class): ' . $this->logManager->getLogger(LoginAuditService::class)->getName());

        $io->success('Both messages are in var/log/typo3_security_*.log');

        return Command::SUCCESS;
    }
}
