<?php

declare(strict_types=1);

use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\FileWriter;

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
