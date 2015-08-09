# Scheduler

***

## Initialise

To start scheduling your workers, you need to initialise the scheduler.

```sh
app/console simpleq:scheduler:init
```

## Control

After successfully initialising the scheduler you can run the following commands to control the scheduler.

```sh
app/console simpleq:scheduler:start
app/console simpleq:scheduler:stop
```

The Scheduler is registering started workers in his working queue. This queue can be checked by 

```sh
simpleq:scheduler:status
```

## Reset & History

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

## Info

An detailed overview of all registered and running workers can be found by executing

```sh
simpleq:worker:status
```
