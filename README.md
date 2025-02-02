## What is it?
[![Build Status](https://travis-ci.org/php-service-bus/service-bus.svg?branch=v3.3)](https://travis-ci.org/php-service-bus/service-bus)
[![Code Coverage](https://scrutinizer-ci.com/g/php-service-bus/service-bus/badges/coverage.png?b=v3.3)](https://scrutinizer-ci.com/g/php-service-bus/service-bus/?branch=v3.3)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/php-service-bus/service-bus/badges/quality-score.png?b=v3.3)](https://scrutinizer-ci.com/g/php-service-bus/service-bus/?branch=v3.3)
[![SymfonyInsight](https://insight.symfony.com/projects/cc0f0f0b-56f1-4ff6-b37d-69ce5db06f32/mini.svg)](https://insight.symfony.com/projects/cc0f0f0b-56f1-4ff6-b37d-69ce5db06f32)
[![Latest Stable Version](https://poser.pugx.org/php-service-bus/service-bus/v/stable)](https://packagist.org/packages/php-service-bus/service-bus)
[![License](https://poser.pugx.org/php-service-bus/service-bus/license)](https://packagist.org/packages/php-service-bus/service-bus)
[![OpenCollective](https://opencollective.com/php-service-bus/sponsors/badge.svg)](https://opencollective.com/php-service-bus)

A concurrency (based on [Amp](https://github.com/amphp)) framework, that lets you implement an asynchronous messaging, a transparent workflow and control of long-lived business transactions by means of the Saga pattern. It implements the [message based architecture](https://www.enterpriseintegrationpatterns.com/patterns/messaging/Messaging.html) and it includes the following patterns: Saga, Publish\Subscribe, Message Bus.

## Main Features
 - Сooperative multitasking
 - Asynchronous messaging (Publish\Subscribe pattern implementation)
 - Event-driven architecture
 - Distribution (messages can be handled by different processes)
   - Subscribers can be implemented on any programming language
 - High performance 
   - [Performance comparison with the "symfony/messenger"](https://github.com/php-service-bus/performance-comparison)
 - Orchestration of long-lived business transactions (for example, a checkout) with the help of Saga Pattern
 - Full history of aggregate changes (EventSourcing)

## Get started
```
composer create-project php-service-bus/skeleton my-project
```
> Demo application (WIP): [service-bus-demo](https://github.com/php-service-bus/demo)

## Documentation
Documentation can be found in the [documentation](https://github.com/php-service-bus/documentation) repository

* [Installation](https://github.com/php-service-bus/documentation/blob/master/pages/installation.md)
* [Basic information](https://github.com/php-service-bus/documentation/blob/master/pages/basic_information.md)
* [Available modules](https://github.com/php-service-bus/documentation/blob/master/pages/available_modules.md)
  * [Sagas](https://github.com/php-service-bus/documentation/blob/master/pages/modules/sagas.md)
  * [Event Sourcing](https://github.com/php-service-bus/documentation/blob/master/pages/modules/event_sourcing.md)
  * [Scheduler](https://github.com/php-service-bus/documentation/blob/master/pages/modules/scheduler.md)
  * [Async RabbitMQ Transport](https://github.com/php-service-bus/documentation/blob/master/pages/modules/transport_phpinnacle.md)
  * [Async Redis Transport](https://github.com/php-service-bus/documentation/blob/master/pages/modules/redis_transport.md)
  * [Async PostgreSQL Database](https://github.com/php-service-bus/documentation/blob/master/pages/modules/storage_amp_sql.md)
* Packages
  * [Http-client](https://github.com/php-service-bus/documentation/blob/master/pages/packages/http_client.md)
  * [Cache](https://github.com/php-service-bus/documentation/blob/master/pages/packages/cache.md)
  * [Mutex](https://github.com/php-service-bus/documentation/blob/master/pages/packages/mutex.md)

## Requirements
  - PHP 7.3+
  - RabbitMQ
  - PostgreSQL 9.5+

## Contributing
Contributions are welcome! Please read [CONTRIBUTING](CONTRIBUTING.md) for details.

## Communication Channels
You can find help and discussion in the following places:
* [Telegram chat (RU)](https://t.me/php_service_bus)
* Create issue [https://github.com/php-service-bus/service-bus/issues](https://github.com/php-service-bus/service-bus/issues)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
