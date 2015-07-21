<?php

namespace DevGarden\simpleq\DemoBundle\Worker;

use DevGarden\simpleq\WorkerBundle\Service\BaseWorker;

class DummyWorker extends BaseWorker
{
    // you might overwrite parent constructor too

    public function prepare(){
        parent::prepare();
        sleep(3);
        print "prepare job".PHP_EOL;
        // use your job preparing code here (f.e. worker configuration)
    }

    public function execute(){
        parent::execute();
        sleep(3);
        print "execute job".PHP_EOL;
        // use your job executing code here (this is the main job process, its possible to only overwrite this parent function)
    }

    public function endJob(){
        print "finishing job".PHP_EOL;
        sleep(3);
        // use your job finalizing code here (f.e. clean up jobs)
        parent::endJob();
    }
}