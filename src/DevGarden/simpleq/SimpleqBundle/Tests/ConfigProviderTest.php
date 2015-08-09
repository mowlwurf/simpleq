<?php

namespace DevGarden\simpleq\SimpleqBundle\Tests;

use DevGarden\simpleq\SimpleqBundle\Service\ConfigProvider;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigProvider
     */
    protected $config;

    public function setUp()
    {
        $this->config = new ConfigProvider([
            'valid' => [
                'type' => 'default',
                'history' => true,
                'worker' => [
                    'dummy' => [
                        'class' => 'DummyClass',
                        'limit' => 10,
                        'retry' => 10
                    ]
                ]
            ],
            'valid2' => [
                'type' => 'default',
                'worker' => [
                    'dummy2' => [
                        'class' => 'Dummy2Class',
                        'limit' => 10
                    ]
                ]
            ]
        ]);
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
            'valid' => [
                'type' => 'default',
                'history' => true,
                'worker' => [
                    'dummy' => [
                        'class' => 'DummyClass',
                        'limit' => 10,
                        'retry' => 10
                    ]
                ]
            ],
            'valid2' => [
                'type' => 'default',
                'worker' => [
                    'dummy2' => [
                        'class' => 'Dummy2Class',
                        'limit' => 10
                    ]
                ]
            ]
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

    public function testGetRetryByWorkerServiceValid(){
        $this->assertEquals(10, $this->config->getWorkerAttributeByServiceId('retry', 'DummyClass'));
    }

    public function testGetRetryByWorkerServiceNoRetryDefined(){
        $this->assertEquals(0, $this->config->getWorkerAttributeByServiceId('retry', 'Dummy2Class'));
    }

    public function testGetWorkerValid()
    {
        $expected = [
            'class' => 'DummyClass',
            'limit' => 10,
            'retry' => 10,
            'queue' => 'valid',
            'name' => 'dummy'
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
                'name' => 'dummy'
            ],
            [
                'class' => 'Dummy2Class',
                'limit' => 10,
                'queue' => 'valid2',
                'name' => 'dummy2'
            ]
        ];
        $this->assertEquals($expected, $this->config->getWorkerList());
    }

    public function testGetQueueHistoryAttributeByQueueIdTrue(){
        $this->assertTrue($this->config->getQueueAttributeByQueueId('history', 'valid'));
    }

    public function testGetQueueHistoryAttributeByQueueIdFalse(){
        $this->assertEquals(0, $this->config->getQueueAttributeByQueueId('history', 'valid2'));
    }
}