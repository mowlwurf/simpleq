<?php

namespace simpleq\SchedulerBundle\Tests;

namespace simpleq\SchedulerBundle\Entity\WorkingQueue;
namespace simpleq\SimpleqBundle\Service\ConfigProvider;
namespace simpleq\SimpleqBundle\Tests\DBTestCase;
namespace simpleq\WorkerBundle\Extension\WorkerStatus;
namespace simpleq\SchedulerBundle\Service\WorkerProvider;
use Doctrine\DBAL\Connection;

class WorkerProviderTest extends DBTestCase
{
    /**
     * @var WorkerProvider
     */
    protected $workerProvider;

    /**
     * @var \AppKernel
     */
    protected $kernel;

    /**
     * @var array
     */
    protected $testDataObj;

    /**
     * @var array
     */
    protected $testDataArr;

    public function setUp()
    {
        $this->kernel = new \AppKernel('test', false);
        $this->kernel->boot();

        $this->workerProvider = new WorkerProvider(
            new ConfigProvider([
                'valid' => [
                    'type' => 'default',
                    'worker' => [
                        'dummy' => [
                            'class' => 'DummyClass',
                            'limit' => 10,
                            'retry' => 10
                        ]
                    ]
                ],
                'valid_two' => [
                    'type' => 'default',
                    'worker' => [
                        'dummy_two' => [
                            'class' => 'Dummy2Class',
                            'limit' => 10
                        ]
                    ]
                ]
            ]),
            $this->getDoctrine()
        );

        // persist testdata
        $testData = $this->getDataSet();
        $data = $testData->getTable('working_queue');
        $c = $data->getRowCount();
        for ($i = 0; $i < $c; $i++) {
            $row = $data->getRow($i);
            $this->persistTestEntry($row);
        }
    }

    public function tearDown()
    {
        $connection = $this->getDoctrine();
        $connection->exec('DELETE FROM working_queue');
        $this->testDataArr = null;
    }

    public function testGetActiveWorkerCountWithoutName()
    {
        $this->assertEquals(4, $this->workerProvider->getActiveWorkerCount());
    }

    public function testGetActiveWorkerCountWithName()
    {
        $this->assertEquals(2, $this->workerProvider->getActiveWorkerCount('DummyClass'));
    }

    public function testGetActiveWorkerCountWithWrongName()
    {
        $this->assertEquals(0, $this->workerProvider->getActiveWorkerCount('foo'));
    }

    public function testGetActiveWorkersWithoutName()
    {
        $this->assertEquals($this->testDataArr, $this->workerProvider->getActiveWorkers());
    }

    public function testGetActiveWorkersWithName()
    {
        array_pop($this->testDataArr);
        array_pop($this->testDataArr);
        $this->assertEquals($this->testDataArr, $this->workerProvider->getActiveWorkers('DummyClass'));
    }

    public function testGetActiveWorkersWithNameNotExist()
    {
        $this->assertEquals([], $this->workerProvider->getActiveWorkers('foo'));
    }

    public function testGetWorkingQueueEntryByPid()
    {
        $this->assertEquals(array_shift($this->testDataArr), $this->workerProvider->getWorkingQueueEntryByPid(21));
    }

    public function testGetWorkingQueueEntryByPidNotExist()
    {
        $this->assertEquals(false, $this->workerProvider->getWorkingQueueEntryByPid(1));
    }

    public function testClearQueueWithName()
    {
        $this->workerProvider->clearQueue('DummyClass');
        array_shift($this->testDataArr);
        array_shift($this->testDataArr);
        $this->assertEquals($this->testDataArr, $this->workerProvider->getActiveWorkers());
    }

    public function testClearQueueWithoutName()
    {
        $this->workerProvider->clearQueue();
        $this->assertEquals([], $this->workerProvider->getActiveWorkers());
    }

    public function testClearQueueWithNameNotExist()
    {
        $this->workerProvider->clearQueue('foo');
        $this->assertEquals($this->testDataArr, $this->workerProvider->getActiveWorkers());
    }

    public function testPushWorkerToWorkingQueue()
    {
        $this->assertRegExp('/^[a-zA-Z0-9]+$/', $this->workerProvider->pushWorkerToWorkingQueue('DummyClass'));
        $this->assertEquals(3, $this->workerProvider->getActiveWorkerCount('DummyClass'));
    }

    public function testUpdateWorkerPid()
    {
        $this->workerProvider->updateWorkerPid(
            $this->workerProvider->pushWorkerToWorkingQueue('DummyClass'),
            2
        );
        $this->assertNotFalse($this->workerProvider->getWorkingQueueEntryByPid(2));
    }

    public function testUpdateWorkerPidInvalidTempPid()
    {
        $this->workerProvider->updateWorkerPid(
            'invalid',
            2
        );
        $this->assertFalse($this->workerProvider->getWorkingQueueEntryByPid(2));
    }

    public function testRemoveWorkingQueueEntry()
    {
        $this->workerProvider->removeWorkingQueueEntry(21);
        array_shift($this->testDataArr);
        $this->assertEquals($this->testDataArr, $this->workerProvider->getActiveWorkers());
    }

    public function testRemoveWorkingQueueEntryInvalid()
    {
        $this->workerProvider->removeWorkingQueueEntry(1);
        $this->assertEquals($this->testDataArr, $this->workerProvider->getActiveWorkers());
    }

    public function testPushWorkerStatus()
    {
        $this->workerProvider->pushWorkerStatus(21, WorkerStatus::WORKER_STATUS_SUCCESS_CODE);
        $statement = <<<'SQL'
SELECT status FROM working_queue WHERE pid = 21
SQL;
        $prepareStatement = $this->getDoctrine()->prepare($statement);
        $prepareStatement->execute();
        $this->assertEquals(WorkerStatus::WORKER_STATUS_SUCCESS_CODE, $prepareStatement->fetchColumn());
    }

    public function testGetWorkerQueue()
    {
        $this->assertEquals('valid', $this->workerProvider->getWorkerQueue('DummyClass'));
    }

    public function testGetWorkerQueueNotExist()
    {
        $this->assertFalse($this->workerProvider->getWorkerQueue('foo'));
    }

    public function testGetWorkerRetry()
    {
        $this->assertEquals(10, $this->workerProvider->getWorkerRetry('DummyClass'));
    }

    public function testGetWorkerRetryNotExist()
    {
        $this->assertFalse($this->workerProvider->getWorkerRetry('foo'));
    }

    public function testGetWorkerTask()
    {
        $this->assertEquals(null, $this->workerProvider->getWorkerTask('DummyClass'));
    }

    public function testGetWorkerTaskNotExist()
    {
        $this->assertFalse($this->workerProvider->getWorkerTask('foo'));
    }

    /**
     * @return Connection
     */
    protected function getDoctrine()
    {
        return $this->kernel->getContainer()->get('doctrine')->getConnection();
    }

    /**
     * @param array $data
     */
    protected function persistTestEntry(array $data)
    {
        $entry = new WorkingQueue();
        $entry->setPid($data['pid']);
        $entry->setStatus($data['status']);
        $entry->setWorker($data['worker']);
        $entry->setCreated(new \DateTime($data['created']));
        $entry->setUpdated(new \DateTime($data['updated']));

        $this->testDataArr[] = $data;

        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($entry);
        $em->flush();
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        $time = new \DateTime();
        $timeFormat = $time->format('Y-m-d h:i:s');

        return $this->createArrayDataSet([
            'working_queue' => [
                [
                    'id' => '1',
                    'status' => '100',
                    'pid' => '21',
                    'worker' => 'DummyClass',
                    'created' => $timeFormat,
                    'updated' => $timeFormat
                ],
                [
                    'id' => '2',
                    'status' => '500',
                    'pid' => '22',
                    'worker' => 'DummyClass',
                    'created' => $timeFormat,
                    'updated' => $timeFormat
                ],
                [
                    'id' => '3',
                    'status' => '200',
                    'pid' => '23',
                    'worker' => 'Dummy2Class',
                    'created' => $timeFormat,
                    'updated' => $timeFormat
                ],
                [
                    'id' => '4',
                    'status' => '300',
                    'pid' => '24',
                    'worker' => 'Dummy2Class',
                    'created' => $timeFormat,
                    'updated' => $timeFormat
                ],
            ]
        ]);
    }
}
