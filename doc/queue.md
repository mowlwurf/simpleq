# Queue

***

## Queue creation

### Create a new queue config

Add your new queue to your SimpleQ config block in your projects config.yml.

```yml 
simpleq:
    queue:
        dummy:                                      // name of queue
            type: default                           // type of queue [default, chain]
            history: true                           // activates history table for queue (optional default: false)
            delete_on_failure: false                // deactivates job deletion when job failed, to enable custom failure handling (optional default: true)
            worker:                                 // set of workers registered to queue
                dummy:                              // name of first (maybe only) worker
                    class: simpleq.worker.dummy     // service id of worker service class
                    task: dummyTask                 // task id for which worker is registered (optional default: null)
                    limit: 10                       // limit of active workers at once, of given type (optional default: 10)
                    retry: 100                      // number of retries on failure (optional default: 0)
                    max_load: 100                   // limits worker spawn to server load
        chain:
            type: chain
            history: true                           // entity will be archived after last chain task has been processed
            worker: 
                first:                              // first worker with task one limited by 2 parallel processes and 5 retries on failure
                    class: your.first.service.id
                    task: one                       // after task one is processed, the base worker interface will trigger an update of job task and data
                    limit: 2
                    retry: 5
                second:                             // second worker with task two limited by 5 parallel processes without any retries on failure
                    class: your.second.service.id
                    task: two
                    limit: 5
```
(For more information, have a look here [WorkerChapter](doc/worker.md))

***

#### QueueAttributes

##### type
The type of a queue defines the worker processing order. Default means there is no dependency between workers,
and any worker could be started at any time.
The chain type defines a queue holding a fix order of processing tasks. 
e.g. the job item should be processed by first worker initially and after this it should be automatically processed by the second worker.
Given order in config reflects processing order, for queue type chain.

##### history (bool : false)
With this flag you can enable queue history for your queue. Finished jobs will be automatically be archived.
Failed jobs only get archived if 'delete_on_failure' flag is true.

##### delete_on_failure (bool : true)
This flag defines, whether to delete a failed job automatically or not.

***

#### WorkerAttributes

##### class
Use a existent service id to define worker class.

##### task (string : null)
Defines a specific task for which a worker is registered.

##### limit (int : 10)
Defines limit for spawning workers of given type.

##### retry (int : 0)
Defines retries to attempt on job failure for each worker.

***

### Create your new configured queue

After adding your queue config block, you just need to execute the following command to create your new queue.

```sh
app/console simpleq:queue:create dummy
```

That's it! 

You can fill the queue with jobs now. e.g. with your own custom persist command. 
For demonstration you should have a look to the following demo command.

```sh
app/console simpleq:demo:persist n
```

For Job creation you can either use the SimpleQ JobBuilder

```php
$builder = new JobBuilder($doctrineConnection);
$builder->create($queue); // queue represents queue id, for the given config example, it would be 'dummy' or 'chain'
$builder->setTask('doSth'); // set job task (optional)
$builder->setData('someData'); // set job data (optional)
$builder->persist();
```

or, of course you can simply persist a job by a code snippet like the following
```php
$job = new Dummy();
$job->setTask('download');
$job->setStatus('open');
$job->setData(json_encode($data));
$job->setCreated(new \DateTime());
$job->setUpdated(new \DateTime());
$this->getContainer()->get('doctrine')->getManager()->persist($job);
$this->getContainer()->get('doctrine')->getManager()->flush();
```

***

## Check

You can check your queue entries with

```sh
simpleq:queue:check [<queue_name>]
```

## Reset

If you want to clear a queue, execute

```sh
simpleq:queue:clear [<queue_name>]
```
