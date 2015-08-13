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

The given example would inject a custom service to the worker. After this, the prepare function will e.g. validate data, while worker status is on 'pending'.

```php
class DummyWorker extends BaseWorker
{
    public function __construct(YourService $service)
    {
        $this->service = $service;
    }
    public function prepare()
    {
        return $this->service->validate($this->data);
    }
    public function execute()
    {
        return $this->processData($this->data);
    }
    public function endJob()
    {
        return $this->cleanUp();
    }
}
```

If workers are processing in chain, $this->data will be updated to job data field, after endJob function, when task and status are updated too, for next worker in chain.