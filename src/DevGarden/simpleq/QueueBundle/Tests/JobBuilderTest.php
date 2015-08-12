<?php

namespace DevGarden\simpleq\QueueBundle\Tests;

use DevGarden\simpleq\QueueBundle\Process\CreateDoctrineEntityProcess;
use DevGarden\simpleq\QueueBundle\Service\JobBuilder;
use DevGarden\simpleq\QueueBundle\Service\QueueProvider;
use DevGarden\simpleq\SimpleqBundle\Service\ConfigProvider;
use DevGarden\simpleq\SimpleqBundle\Tests\DBTestCase;

class JobBuilderTest extends DBTestCase
{
    /**
     * @var \AppKernel
     */
    protected $kernel;

    public function setUp()
    {
        $this->kernel = new \AppKernel('test', false);
        $this->kernel->boot();
    }

    public function testJobBuilder(){
        $builder = new JobBuilder($this->kernel->getContainer()->get('doctrine')->getConnection());
        $builder->create('valid');
        $builder->setData('testData');
        $builder->setTask('testTask');
        $builder->persist();

        $provider = new QueueProvider(
            new ConfigProvider([
                'valid' => [
                    'type' => 'default',
                    'worker' => [
                        'dummy' => [
                            'class' => 'DummyClass',
                            'limit' => 10
                        ]
                    ]
                ],
                'validTwo' => [
                    'type' => 'default',
                    'history' => true,
                    'worker' => [
                        'dummy_two' => [
                            'class' => 'Dummy2Class',
                            'limit' => 10
                        ]
                    ]
                ]
            ]),
            $this->getEntityProcess(),
            $this->kernel->getContainer()->get('doctrine')->getConnection()
        );
        $entries  = $provider->getQueueEntries('valid');
        $this->assertEquals(1, count($entries));
        $this->assertEquals('open', $entries[0]['status']);
        $this->assertEquals('testTask', $entries[0]['task']);
        $this->assertEquals('testData', $entries[0]['data']);
        $this->assertRegExp('/[\d+]{4}-[\d+]{2}-[\d+]{2}\s[\d+]{2}:[\d+]{2}:[\d+]{2}/', $entries[0]['created']);
        $this->assertNotEquals('0000-00-00 00:00:00', $entries[0]['created']);
        $this->assertRegExp('/[\d+]{4}-[\d+]{2}-[\d+]{2}\s[\d+]{2}:[\d+]{2}:[\d+]{2}/', $entries[0]['updated']);
        $this->assertNotEquals('0000-00-00 00:00:00', $entries[0]['updated']);
    }

    public function tearDown(){
        $connection = $this->kernel->getContainer()->get('doctrine')->getConnection();
        $connection->exec('DELETE FROM valid');
    }

    /**
     * @return CreateDoctrineEntityProcess
     */
    protected function getEntityProcess()
    {
        return $this->kernel->getContainer()->get('simpleq.queue.create.process');
    }

    /**
     * @return QueueProvider
     */
    protected function getQueueProvider(){
        return $this->kernel->getContainer()->get('simpleq.queue.provider');
    }

    public function getDataSet(){

    }
}