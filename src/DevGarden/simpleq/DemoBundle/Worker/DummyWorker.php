<?php

namespace DevGarden\simpleq\DemoBundle\Worker;

use DevGarden\simpleq\WorkerBundle\Service\BaseWorker;

class DummyWorker extends BaseWorker
{

    public function prepare()
    {
        print "prepare job" . PHP_EOL;

        // use your job preparing code here (f.e. worker configuration)
    }

    public function execute()
    {
        print "execute job" . PHP_EOL;
        $data = json_decode($this->data);
        if (!file_put_contents(__DIR__ . '/../../../../../images/' . md5($data->url) . '.jpg',
            file_get_contents($data->url))
        ) {
            print 'Cant put file';
            throw new \Exception('Cant put file');
        }

        return json_encode(['file' => __DIR__ . '/../../../../../images/' . md5($data->url) . '.jpg']);
        // use your job executing code here (this is the main job process, its possible to only overwrite this parent function)
    }

    public function endJob()
    {
        print "finishing job" . PHP_EOL;

        // use your job finalizing code here (f.e. clean up jobs)
    }
}