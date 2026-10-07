# Logging API in TYPO3

## What does it do?

1.0.0 Get a logger via dependency injection and log one message per log level.

1.1.0 Shows how logger names (components) are built and compares LogManager and LoggerAwareInterface.

1.2.0 Configures own writers for the extension namespace and shows the effective writers per logger.

1.3.0 Groups loggers of different classes into the "security" channel via the #[Channel] attribute.

1.4.0 Writes one record with the DatabaseWriter, RotatingFileWriter and PhpErrorLogWriter.

1.5.0 Logs placeholders, context data and exceptions and compares the file with the database.

1.6.0 Enriches log records with the memory usage, web and introspection processor.

## Installation

Add via composer:

    composer require "passionweb/logging-api"

* Install the extension via composer
* Flush TYPO3 and PHP Cache

Every episode adds one command, run it with `vendor/bin/typo3 codebreak:log:<name>`.

## Requirements

This example uses no 3rd party libraries.

## Extension settings

There are no extension settings available.

## Troubleshooting and logging

If something does not work as expected take a look at the log file.
Every problem is logged to the TYPO3 log (normally found in `var/log/typo3_*.log`)

## Achieving more together or Feedback, Feedback, Feedback

I'm grateful for any feedback! Be it suggestions for improvement, requests or just a (constructive) feedback on how good or crappy this snippet/repo is.

Feel free to send me your feedback to [service@passionweb.de](mailto:service@passionweb.de "Send Feedback") or [contact me on Slack](https://typo3.slack.com/team/U02FG49J4TG "Contact me on Slack")
