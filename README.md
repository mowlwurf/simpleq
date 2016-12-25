SimpleQ
=======

[![Coverage Status](https://coveralls.io/repos/mowlwurf/simpleq/badge.svg?branch=master&service=github)](https://coveralls.io/github/mowlwurf/simpleq?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/55cb799adfed0a001f0000f5/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55cb799adfed0a001f0000f5)

[![Code Climate](https://codeclimate.com/repos/55e58b086956805aff007180/badges/0d7c2cdac7bd3498d630/gpa.svg)](https://codeclimate.com/repos/55e58b086956805aff007180/feed)
[![Latest Stable Version](https://poser.pugx.org/devgarden/simpleq/v/stable)](https://packagist.org/packages/devgarden/simpleq) 
[![License](https://poser.pugx.org/devgarden/simpleq/license)](https://packagist.org/packages/devgarden/simpleq)

***

SimpleQ is a generic queue system based on Symfony2 & Doctrine. Lightweight, fast and easy to configure.

***

# Dependencies

Your project should be able to run with the following dependencies:

**For Symfony 2**

v0 - v0.9.7

- "php": ">=5.3.9"
- "symfony/symfony": "2.7.*"
- "doctrine/orm": "~2.2"
- "doctrine/dbal": "~2.2"
- "doctrine/doctrine-bundle": "~1.4"
- "gedmo/doctrine-extensions": "~2.4"

**For Symfony 3**

v1 - latest
- "php": ">=5.3.9",
- "symfony/symfony": "3.*",
- "doctrine/orm": "^2.5",
- "doctrine/dbal": "^2.5",
- "doctrine/doctrine-bundle": "~1.6",
- "gedmo/doctrine-extensions": "~2.4"

***

# Setup

```composer require devgarden/simpleq```

***

# Usage

###### Learn how to register, create and handle queues in the [Queue chapter](doc/queue.md)
###### Learn how to initialize, check & control scheduler in the [Scheduler chapter](doc/scheduler.md)
###### Learn how to build and run your worker in the [Worker chapter](doc/worker.md)
###### Learn how to cluster queues & workers [Cluster chapter](doc/cluster.md)
###### Have a look at the [Roadmap](doc/roadmap.md)