# Worker

***

## Build

To build your custom worker, extend the BaseWorker to implement logic for automatic scheduler processing.

```php
class DummyWorker extends BaseWorker
{
}
```

***

You can use four functions to execute your code now. 

### __construct

basic construct function could be written without any dependencies

### prepare

contains code which should be executed while worker is on status 'pending'

### execute

contains code which should be executed while worker is on status 'running'

### endJob

contains code which should be executed after the job execution, like cleanup jobs etc.

***

## Example

The given example would inject a custom service to the worker. After this, the prepare function will validate data, while worker status is on 'pending'.

```php
class DummyWorker extends BaseWorker
{
    public function __construct(YourService $service)
    {
        $this->service = $service;
    }
    public function prepare($data)
    {
        return $this->service->validate($data);
    }
    public function execute($data)
    {
        return $this->processData($data);
    }
    public function endJob($data)
    {
        return $this->cleanUp();
    }
}
```

If workers are processing in chain, result of last executed function (prepare|execute|endJob) will be updated to job data field, when task and status are updated too, for next worker in chain.