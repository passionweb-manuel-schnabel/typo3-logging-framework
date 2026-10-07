<?php

declare(strict_types=1);

use Passionweb\LoggingApi\Log\Writer\JsonLinesWriter;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Log\Processor\IntrospectionProcessor;
use TYPO3\CMS\Core\Log\Processor\MemoryUsageProcessor;
use TYPO3\CMS\Core\Log\Processor\WebProcessor;
use TYPO3\CMS\Core\Log\Writer\DatabaseWriter;
use TYPO3\CMS\Core\Log\Writer\Enum\Interval;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
use TYPO3\CMS\Core\Log\Writer\PhpErrorLogWriter;
use TYPO3\CMS\Core\Log\Writer\RotatingFileWriter;
use TYPO3\CMS\Core\Log\Writer\SyslogWriter;

defined('TYPO3') or die();

// The logger name "Passionweb.LoggingApi.Command.LogBasicsCommand" is split at
// the dots and used as a path into $GLOBALS['TYPO3_CONF_VARS']['LOG'].
// The most specific "writerConfiguration" on that path wins, so this entry
// applies to every class below the Passionweb\LoggingApi namespace.
//
// It REPLACES the global configuration, it is not merged with it: warnings of
// this extension no longer reach the default typo3_*.log file.
//
// ??= keeps a configuration a project has already set in config/system/additional.php.
$GLOBALS['TYPO3_CONF_VARS']['LOG']['Passionweb']['LoggingApi']['writerConfiguration'] ??= [
    // The array key is the minimum level, DEBUG means "everything".
    LogLevel::DEBUG => [
        // Writer class => options. logFileInfix results in var/log/typo3_logging_api_<hash>.log
        FileWriter::class => [
            'logFileInfix' => 'logging_api',
        ],
    ],
];

// A channel is just a logger name without dots. Classes from different
// extensions can share it, and one entry configures all of them.
$GLOBALS['TYPO3_CONF_VARS']['LOG']['security']['writerConfiguration'] ??= [
    LogLevel::INFO => [
        FileWriter::class => [
            'logFileInfix' => 'security',
        ],
    ],
];

// One logger, several writers. Every writer receives every record of its level.
$GLOBALS['TYPO3_CONF_VARS']['LOG']['codebreak']['writerConfiguration'] ??= [
    LogLevel::INFO => [
        // Writes into sys_log, the table behind the "Log" backend module.
        DatabaseWriter::class => [],
        // A FileWriter that starts a new file every day and keeps the last 7.
        RotatingFileWriter::class => [
            'logFileInfix' => 'codebreak',
            'interval' => Interval::DAILY,
            'maxFiles' => 7,
        ],
        // Hands the message to error_log() of PHP, on the CLI that is STDERR.
        PhpErrorLogWriter::class => [],
        // Every writer accepts "disabled", handy to switch one off per environment.
        SyslogWriter::class => [
            'disabled' => true,
        ],
    ],
];

// Processors add data to a record before the writers receive it.
$GLOBALS['TYPO3_CONF_VARS']['LOG']['processors']['writerConfiguration'] ??= [
    LogLevel::DEBUG => [
        FileWriter::class => [
            'logFileInfix' => 'processors',
        ],
    ],
];
$GLOBALS['TYPO3_CONF_VARS']['LOG']['processors']['processorConfiguration'] ??= [
    // Like writers, the key is the minimum level the processor runs for.
    LogLevel::DEBUG => [
        MemoryUsageProcessor::class => [],
        // Adds request URL, host, user agent ... but only if there is a request.
        WebProcessor::class => [],
    ],
    // A backtrace is expensive, so only errors and worse get one.
    LogLevel::ERROR => [
        IntrospectionProcessor::class => [],
    ],
];

// An own writer is configured exactly like a core writer.
$GLOBALS['TYPO3_CONF_VARS']['LOG']['json']['writerConfiguration'] ??= [
    LogLevel::DEBUG => [
        JsonLinesWriter::class => [
            'logFile' => 'codebreak.jsonl',
        ],
    ],
];
