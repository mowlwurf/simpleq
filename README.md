simpleq
=======

A Symfony bundle to create & handle queue for any needs. Configuration via config.yml.

Usage
=====

Create a queue
===============

```yml 
config.yml
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
app/console simpleq:queue:create dummy
app/console simpleq:demo:persist n
app/console simpleq:scheduler:init
app/console simpleq:scheduler:start
```