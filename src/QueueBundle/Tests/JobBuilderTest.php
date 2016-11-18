<?php

namespace simpleq\QueueBundle\Tests;

namespace simpleq\QueueBundle\Process\CreateDoctrineEntityProcess;
namespace simpleq\QueueBundle\Service\JobBuilder;
namespace simpleq\QueueBundle\Service\QueueProvider;
namespace simpleq\SimpleqBundle\Service\ConfigProvider;
namespace simpleq\SimpleqBundle\Tests\DBTestCase;

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
        $builder->flush();

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

        $reflection              = new \ReflectionClass($builder);
        $reflectionPropertyJob   = $reflection->getProperty('job');
        $reflectionPropertyQueue = $reflection->getProperty('queue');

        $reflectionPropertyJob->setAccessible(true);
        $reflectionPropertyQueue->setAccessible(true);

        $this->assertNull($reflectionPropertyJob->getValue($builder));
        $this->assertNull($reflectionPropertyQueue->getValue($builder));
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