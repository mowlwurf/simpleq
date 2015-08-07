simpleq
=======
***

A Symfony bundle to create & handle queues for any needs. Configuration via config.yml.

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

***

# Roadmap

***

| Vision | Version | Status  | Note |
|--------|---------|---------|------|
| first running prototype | (v0.1) | :construction: | including processing bugs and memory leaks |
| removed scheduler process conflicts, by moving worker registration and job status trigger to scheduler | (v0.2) | :ballot_box_with_check: | no processing bugs |
| optimized scheduler process, added indices, deactivated doctrine logging and profiling | (v0.3) | :ballot_box_with_check: | no memory leaks |
| performance optimization of scheduler | (v0.4) | :trophy: | x100 faster, xn cheaper |
| queue or worker config attribute retry int $times | (v0.5) | :grey_question: | |
| enable queue & config task handling | (v0.6) | :grey_question: | |
| testing db for phpunit | (~v0.7) | :grey_question: | |
| enable chainbehaviour | (~v0.8) | :grey_question: | |
| full featured stable release | (v1.0) | :grey_question: | |
| webinterface to show queue,worker & scheduler status | (v1.1) | :grey_question: | maybe extra package |