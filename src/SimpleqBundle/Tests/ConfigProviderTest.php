<?php

namespace simpleq\SimpleqBundle\Tests;

namespace simpleq\SimpleqBundle\Service\ConfigProvider;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigProvider
     */
    protected $config;

    public function setUp()
    {
        $this->config = new ConfigProvider(
            [
                'valid'  => [
                    'type'    => 'default',
                    'history' => true,
                    'worker'  => [
                        'dummy' => [
                            'class' => 'DummyClass',
                            'limit' => 10,
                            'retry' => 10,
                        ],
                    ],
                ],
                'valid2' => [
                    'type'              => 'default',
                    'delete_on_failure' => false,
                    'worker'            => [
                        'dummy2' => [
                            'class'    => 'Dummy2Class',
                            'limit'    => 10,
                            'max_load' => 100,
                        ],
                    ],
                ],
                'valid3' => [
                    'type'              => 'chain',
                    'delete_on_failure' => false,
                    'worker'            => [
                        'dummy'  => [
                            'class' => 'DummyClass',
                            'limit' => 10,
                            'retry' => 10,
                            'task'  => 'task_one',
                        ],
                        'dummy2' => [
                            'class' => 'Dummy2Class',
                            'limit' => 10,
                            'task'  => 'task_two',
                        ],
                    ],
                ],
            ]
        );
    }

    public function testGetQueueValid()
    {
        $input = 'valid';
        $this->assertNotFalse($this->config->getQueue($input));
    }

    public function testGetQueueInValid()
    {
        $input = 'invalid';
        $this->assertFalse($this->config->getQueue($input));
    }

    public function testGetQueueList()
    {
        $expected = [
            'valid'  => [
                'type'    => 'default',
                'history' => true,
                'worker'  => [
                    'dummy' => [
                        'class' => 'DummyClass',
                        'limit' => 10,
                        'retry' => 10,
                    ],
                ],
            ],
            'valid2' => [
                'type'              => 'default',
                'delete_on_failure' => false,
                'worker'            => [
                    'dummy2' => [
                        'class'    => 'Dummy2Class',
                        'limit'    => 10,
                        'max_load' => 100,
                    ],
                ],
            ],
            'valid3' => [
                'type'              => 'chain',
                'delete_on_failure' => false,
                'worker'            => [
                    'dummy'  => [
                        'class' => 'DummyClass',
                        'limit' => 10,
                        'retry' => 10,
                        'task'  => 'task_one',
                    ],
                    'dummy2' => [
                        'class' => 'Dummy2Class',
                        'limit' => 10,
                        'task'  => 'task_two',
                    ],
                ],
                'task_chain'        => ['task_one', 'task_two'],
            ],
        ];
        $this->assertEquals($expected, $this->config->getQueueList());
    }

    public function testGetQueueByWorkerServiceValid()
    {
        $this->assertEquals('valid', $this->config->getWorkerAttributeByServiceId('queue', 'DummyClass'));
    }

    public function testGetQueueByWorkerServiceInValid()
    {
        $this->assertFalse($this->config->getWorkerAttributeByServiceId('queue', 'invalid'));
    }

    public function testGetRetryByWorkerServiceValid()
    {
        $this->assertEquals(10, $this->config->getWorkerAttributeByServiceId('retry', 'DummyClass'));
    }

    public function testGetRetryByWorkerServiceNoRetryDefined()
    {
        $this->assertEquals(0, $this->config->getWorkerAttributeByServiceId('retry', 'Dummy2Class'));
    }

    public function testGetWorkerValid()
    {
        $expected = [
            'class' => 'DummyClass',
            'limit' => 10,
            'retry' => 10,
            'queue' => 'valid',
            'name'  => 'dummy',
        ];
        $this->assertEquals($expected, $this->config->getWorker('valid', 'dummy'));
    }

    public function testGetWorkerInValid()
    {
        $this->assertFalse($this->config->getWorker('valid', 'invalid'));
    }

    public function testGetWorkerList()
    {
        $expected = [
            [
                'class' => 'DummyClass',
                'limit' => 10,
                'retry' => 10,
                'queue' => 'valid',
                'name'  => 'dummy',
            ],
            [
                'class'    => 'Dummy2Class',
                'limit'    => 10,
                'max_load' => 100,
                'queue'    => 'valid2',
                'name'     => 'dummy2',
            ],
            [
                'class' => 'DummyClass',
                'limit' => 10,
                'retry' => 10,
                'task'  => 'task_one',
                'queue' => 'valid3',
                'name'  => 'dummy',
            ],
            [
                'class' => 'Dummy2Class',
                'limit' => 10,
                'task'  => 'task_two',
                'queue' => 'valid3',
                'name'  => 'dummy2',
            ],
        ];
        $this->assertEquals($expected, $this->config->getWorkerList());
    }

    public function testGetQueueHistoryAttributeByQueueIdTrue()
    {
        $this->assertTrue($this->config->getQueueAttributeByQueueId('history', 'valid'));
    }

    public function testGetQueueHistoryAttributeByQueueIdFalse()
    {
        $this->assertEquals(0, $this->config->getQueueAttributeByQueueId('history', 'valid2'));
    }

    public function testGetQueueHistoryAttributeByQueueIdNotExist()
    {
        $this->assertEquals(false, $this->config->getQueueAttributeByQueueId('history', 'invalid'));
    }

    public function testGetQueueDeleteOnFailureAttributeByQueueIdTrue()
    {
        $this->assertTrue($this->config->getQueueAttributeByQueueId('delete_on_failure', 'valid'));
    }

    public function testGetQueueDeleteOnFailureAttributeByQueueIdFalse()
    {
        $this->assertEquals(0, $this->config->getQueueAttributeByQueueId('delete_on_failure', 'valid2'));
    }

    public function testGetQueueDeleteOnFailureAttributeByQueueIdNotExist()
    {
        $this->assertEquals(false, $this->config->getQueueAttributeByQueueId('delete_on_failure', 'invalid'));
    }

    public function testQueueTypeChainAttributeByQueueIdTrue()
    {
        $this->assertEquals('chain', $this->config->getQueueAttributeByQueueId('type', 'valid3'));
    }

    public function testQueueTypeChainAttributeByQueueIdFalse()
    {
        $this->assertEquals('default', $this->config->getQueueAttributeByQueueId('type', 'valid'));
    }

    public function testQueueTypeChainAttributeByQueueIdInvalid()
    {
        $this->assertEquals(false, $this->config->getQueueAttributeByQueueId('type', 'invalid'));
    }

    public function testGetQueueTaskChainAttributeByQueueIdValidTrue()
    {
        $this->assertEquals(
            ['task_one', 'task_two'],
            $this->config->getQueueAttributeByQueueId('task_chain', 'valid3')
        );
    }

    public function testGetQueueTaskChainAttributeByQueueIdValidFalse()
    {
        $this->assertEquals(0, $this->config->getQueueAttributeByQueueId('task_chain', 'valid'));
    }

    public function testGetQueueTaskChainAttributeByQueueIdInvalid()
    {
        $this->assertEquals(false, $this->config->getQueueAttributeByQueueId('task_chain', 'invalid'));
    }
}