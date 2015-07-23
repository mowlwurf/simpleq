<?php

namespace DevGarden\simpleq\DemoBundle\Worker;

use DevGarden\simpleq\WorkerBundle\Service\BaseWorker;

class DummyWorker extends BaseWorker
{

    public function prepare($data){
        print "prepare job".PHP_EOL;
        var_dump($data);
        usleep(rand(100,1000));
        // use your job preparing code here (f.e. worker configuration)
    }

    public function execute($data){
        print "execute job".PHP_EOL;
        var_dump($data);
        usleep(rand(100,1000));
        // use your job executing code here (this is the main job process, its possible to only overwrite this parent function)
    }

    public function endJob($data){
        print "finishing job".PHP_EOL;
        var_dump($data);
        usleep(rand(100,1000));
        // use your job finalizing code here (f.e. clean up jobs)
    }
}