#simpleq
=======

A Symfony bundle to create & handle queue for any needs. Configuration via config.yml.

#Usage

## Create a queue

```yml 
    simpleq:
	queue:
	    dummy:
		type: default
		worker:
		    dummy:
			class: simpleq.worker.dummy
			limit: 10
```


```sh
# create your configured queue
app/console simpleq:queue:create dummy
# execute your persist job to the queue
app/console simpleq:demo:persist n
# init scheduler, this could be happen earlier too
app/console simpleq:scheduler:init
# start the scheduler and watch it working :)
app/console simpleq:scheduler:start
```

## CheckStatus

```sh
# queues
simpleq:queue:check [<queue_name>]
# scheduler
simpleq:scheduler:status
simpleq:scheduler:history [<worker_service_id>]
# worker
simpleq:worker:status  
```