<?php

declare(strict_types=1);

namespace Passionweb\LoggingApi\Service;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\Channel;

// #[Channel] on the class: every logger injected into this class is named
// "security" instead of "Passionweb.LoggingApi.Service.LoginAuditService".
#[Channel('security')]
final readonly class LoginAuditService
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function failedLogin(string $username): void
    {
        $this->logger->warning('Failed login for "' . $username . '"');
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
