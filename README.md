simpleq
=======

[![Build Status](https://travis-ci.org/mowlwurf/simpleq.png?branch=master)](https://travis-ci.org/mowlwurf/simpleq)
[![Coverage Status](https://coveralls.io/repos/mowlwurf/simpleq/badge.svg?branch=master&service=github)](https://coveralls.io/github/mowlwurf/simpleq?branch=master)
[![Dependency Status](https://www.versioneye.com/php/devgarden:simpleq/badge.svg)](https://www.versioneye.com/php/devgarden:simpleq)

[![Latest Stable Version](https://poser.pugx.org/devgarden/simpleq/v/stable)](https://packagist.org/packages/devgarden/simpleq) 
[![Total Downloads](https://poser.pugx.org/devgarden/simpleq/downloads)](https://packagist.org/packages/devgarden/simpleq) 
[![Latest Unstable Version](https://poser.pugx.org/devgarden/simpleq/v/unstable)](https://packagist.org/packages/devgarden/simpleq) 
[![License](https://poser.pugx.org/devgarden/simpleq/license)](https://packagist.org/packages/devgarden/simpleq)

***

Generic queue system based on Symfony2. Light weight, easy and fast to configure and initiate.

***

# Dependencies

Your project should be able to run with the following dependencies:

- "php": ">=5.3.9"
- "symfony/symfony": "2.7.*"
- "doctrine/orm": "~2.2,>=2.2.3,<2.5"
- "doctrine/dbal": "<2.5"
- "doctrine/doctrine-bundle": "~1.4"
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

***

# Roadmap

***

| Vision | Version | Status  | Note |
|--------|---------|---------|------|
| first running prototype | (v0.1-alpha) | :moyai: | including processing bugs and memory leaks |
| removed scheduler process conflicts, by moving worker registration and job status trigger to scheduler | (v0.2-alpha) | :ballot_box_with_check: | no processing bugs |
| optimized scheduler process, added indices, deactivated doctrine logging and profiling | (v0.3-alpha) | :ballot_box_with_check: | no memory leaks |
| performance optimization of scheduler | (v0.4-alpha) | :rocket: | x100 faster, xn cheaper |
| queue or worker config attribute retry int $times | (v0.5-alpha) | :ballot_box_with_check: | failed jobs stay in queue to enable re-queuing |
| enable queue & config task handling | (v0.5-alpha) | :ballot_box_with_check: | got resolved one version earlier |
| testing db for phpunit | (v0.5-alpha) | :ballot_box_with_check: | providers fully tested now |
| queue option history to create and use job queue history | (v0.6-alpha) | :ballot_box_with_check: | |
| queue option delete_on_failure to enable custom handling for failed jobs | (v0.6-alpha) | :ballot_box_with_check: | |
| enable chainbehaviour | (v0.7-beta) | :link: | doc extension follows |
| performance optimization workerinterface/baseworker | (v0.8-beta) | :trophy: | worker interface performance optimized by factor 6-7 |
| final tests & bugfixes, extend doc | (v0.9-beta) | :construction: | |
| full featured stable release | (v1.0) | :grey_question: | |
| webinterface to show queue,worker & scheduler status | (v1.1) | :grey_question: | maybe extra package |