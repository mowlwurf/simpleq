#simpleq
=======

A Symfony bundle to create & handle queues for any needs. Configuration via config.yml.

#Dependencies

Your project should be able to run with the following dependencies:

- "php": ">=5.3.9"
- "symfony/symfony": "2.7.*"
- "gedmo/doctrine-extensions": "~2.4"

#Setup

```composer require devgarden/simpleq```

#Usage

## Create a new queue config

Add your new queue to your simpleq config block in your projects config.yml.

```yml 
simpleq:
    queue:
        dummy:                                      // name of queue
            type: default                           // type of queue [default, chain]
            worker:                                 // set of workers registered to queue
                dummy:                              // name of first (maybe only) worker
                    class: simpleq.worker.dummy     // service id of worker service class
                    limit: 10                       // limit of active workers at once, of given type
```

### type
The type of a queue defines the worker processing order. Default means there is no dependency between workers,
and any worker could be started at any time.
The chain type defines a queue holding a fix order of processing tasks. 
f.e. the job item should be processed by first worker initially and after this it should be automatically processed by the second worker.
Given order in config reflects processing order, for queue type chain.

## Create the new queue

After you added your queue config block, you just need to execute the following command to create your new queue.

```sh
app/console simpleq:queue:create dummy
```

That's it! You can fill the queue with jobs now.
f.e.

```sh
app/console simpleq:demo:persist n
```

You can check your queue entries with

```sh
simpleq:queue:check [<queue_name>]
```

If you want to clear a queue, execute

```sh
simpleq:queue:clear [<queue_name>]
```

## Scheduler

To start scheduling your workers, you need to initialise the scheduler.

```sh
app/console simpleq:scheduler:init
```

After successfully initialising the scheduler you can run the following commands to control the scheduler.

```sh
app/console simpleq:scheduler:start
app/console simpleq:scheduler:stop
```

Scheduler is registering started workers to his working queue. This queue can be checked by 

```sh
simpleq:scheduler:status
```

If you want to clear this working queue, execute

```sh
simpleq:scheduler:clear
```

Also the scheduler provides a history for finished workers, which can be read with (optional: worker_service_id)

```sh
simpleq:scheduler:history [<worker_service_id>]
```

If you want to clear this history, execute

```sh
simpleq:scheduler:clear:history
```

or for a given worker

```sh
simpleq:scheduler:clear:history [<worker_service_id>]
```

An detailed overview of all registered and running workers can be found by executing

```sh
simpleq:worker:status
```
