<?php

namespace DevGarden\simpleq\QueueBundle\Tests;

use DevGarden\simpleq\QueueBundle\Entity\Valid;
use DevGarden\simpleq\QueueBundle\Process\CreateDoctrineEntityProcess;
use DevGarden\simpleq\QueueBundle\Service\QueueProvider;
use DevGarden\simpleq\SchedulerBundle\Extension\JobStatus;
use DevGarden\simpleq\SimpleqBundle\Service\ConfigProvider;
use DevGarden\simpleq\SimpleqBundle\Tests\DBTestCase;
use Doctrine\DBAL\Connection;


class QueueProviderTest extends DBTestCase
{
    /**
     * @var QueueProvider
     */
    protected $queueProvider;

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

        $this->queueProvider = new QueueProvider(
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
            $this->getDoctrine()
        );

        // persist testdata
        if (file_exists(__DIR__ . '/../Entity/Valid.php')) {
            $testData = $this->getDataSet();
            $data = $testData->getTable('valid_');
            $c = $data->getRowCount();
            for ($i = 0; $i < $c; $i++) {
                $row = $data->getRow($i);
                $this->persistTestEntry($row);
            }
        }
    }

    public function tearDown()
    {
        $connection = $this->getDoctrine();
        $connection->exec('DELETE FROM valid');
        $this->testDataArr = null;
    }

    public function testGenerateQueueValid()
    {
        $this->expectOutputRegex('/generating\sDevGarden\\\\simpleq\\\\QueueBundle\\\\Entity\\\\Valid/');
        $this->assertFalse($this->hasOutput());
        $this->queueProvider->generateQueue('valid');
        $this->assertTrue(file_exists(__DIR__ . '/../Entity/Valid.php'));

        //TODO update database schema, only working on default environment
        // workaround: table already exists
        /*
        $paths = array(__DIR__ . '/../Entity/');
        $config = Setup::createAnnotationMetadataConfiguration($paths);
        $dbParams = array('driver' => 'pdo_mysql', 'memory' => false);
        $entityManager = EntityManager::create($dbParams, $config);
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();

        if ( ! empty($metaData)) {
            // Create SchemaTool
            $tool = new SchemaTool($entityManager);
            $tool->getUpdateSchemaSql($metaData, true);
            $tool->updateSchema($metaData, true);
        }
        */
    }

    public function testGenerateQueueHistory()
    {
        $this->expectOutputRegex('/generating\sDevGarden\\\\simpleq\\\\QueueBundle\\\\Entity\\\\ValidTwo/');
        $this->assertFalse($this->hasOutput());
        $this->queueProvider->generateQueue('validTwo');
        $this->assertTrue(file_exists(__DIR__ . '/../Entity/ValidTwo.php'));
        $this->assertTrue(file_exists(__DIR__ . '/../Entity/ValidTwoHistory.php'));
    }

    /**
     * @param array $data
     */
    protected function persistTestEntry(array $data)
    {
        $entry = new Valid();
        $entry->setTask($data['task']);
        $entry->setStatus($data['status']);
        $entry->setData($data['data']);
        $entry->setCreated(new \DateTime($data['created']));
        $entry->setUpdated(new \DateTime($data['updated']));

        $this->testDataArr[] = $data;

        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($entry);
        $em->flush();
    }

    /**
     * @expectedException \Exception
     */
    public function testGenerateQueueInvalid()
    {
        $this->queueProvider->generateQueue('invalid');
        $this->assertFalse(file_exists(__DIR__ . '/../Entity/Invalid.php'));
    }

    public function testGetQueueEntriesWithoutTask()
    {
        $entries = $this->queueProvider->getQueueEntries('valid');
        $this->assertEquals($this->testDataArr, $entries);
    }

    public function testGetQueueEntriesWithTask()
    {
        $entries = $this->queueProvider->getQueueEntries('valid', 'test');
        array_shift($this->testDataArr);
        array_pop($this->testDataArr);
        $this->assertEquals($this->testDataArr, $entries);
    }

    public function testGetQueueEntriesWithTasks()
    {
        $entries = $this->queueProvider->getQueueEntries('valid', ['test', 'test2']);
        array_shift($this->testDataArr);
        $this->assertEquals($this->testDataArr, $entries);
    }

    public function testGetQueueEntriesWithTaskNotExist()
    {
        $entries = $this->queueProvider->getQueueEntries('valid', 'foo');
        $this->assertEquals([], $entries);
    }

    public function testGetNextOpenQueueEntryWithoutTask()
    {
        $expected = array_shift($this->testDataArr);
        unset($expected['created'], $expected['updated'], $expected['status']);
        $this->assertEquals($expected, $this->queueProvider->getNextOpenQueueEntry('valid'));
    }

    public function testGetNextOpenQueueEntryWithTask()
    {
        $expected = $this->testDataArr[1];
        unset($expected['created'], $expected['updated'], $expected['status']);
        $this->assertEquals($expected, $this->queueProvider->getNextOpenQueueEntry('valid', 'test'));
    }

    public function testGetNextOpenQueueEntryWithTasks()
    {
        $expected = $this->testDataArr[1];
        $this->assertEquals($expected, $this->queueProvider->getNextOpenQueueEntry('valid', ['test', 'test2']));
    }

    public function testGetNextOpenQueueEntryWithTaskNotExist()
    {
        $this->assertFalse($this->queueProvider->getNextOpenQueueEntry('valid', 'foo'));
    }

    public function testRemoveQueueEntryValid()
    {
        $this->queueProvider->removeQueueEntry('valid', 1);
        array_shift($this->testDataArr);
        $this->assertEquals($this->testDataArr, $this->queueProvider->getQueueEntries('valid'));
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     */
    public function testRemoveQueueEntryInvalidQueue()
    {
        $this->queueProvider->removeQueueEntry('invalid', 1);
        $this->assertEquals($this->testDataArr, $this->queueProvider->getQueueEntries('valid'));
    }

    public function testRemoveQueueEntryInvalidId()
    {
        $this->queueProvider->removeQueueEntry('valid', 10);
        $this->assertEquals($this->testDataArr, $this->queueProvider->getQueueEntries('valid'));
    }

    public function testUpdateQueueEntryValid()
    {
        $this->queueProvider->updateQueueEntry('valid', 1, ['status' => JobStatus::JOB_STATUS_FINISHED]);
        $entries = $this->queueProvider->getQueueEntries('valid', ['test', '']);
        $this->assertEquals(JobStatus::JOB_STATUS_FINISHED, $entries[0]['status']);
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     */
    public function testUpdateQueueEntryInvalidQueue()
    {
        $this->queueProvider->updateQueueEntry('invalid', 1, ['status' => JobStatus::JOB_STATUS_FINISHED]);
    }

    public function testUpdateQueueEntryInvalidId()
    {
        $this->queueProvider->updateQueueEntry('valid', 10, ['status' => JobStatus::JOB_STATUS_FINISHED]);
        $entries = $this->queueProvider->getQueueEntries('valid');
        $this->assertEquals(JobStatus::JOB_STATUS_OPEN, $entries[0]['status']);
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     */
    public function testUpdateQueueEntryInvalidArgsNull()
    {
        $this->queueProvider->updateQueueEntry('valid', 1, ['null' => JobStatus::JOB_STATUS_FINISHED]);
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     */
    public function testUpdateQueueEntryInvalidArgsFoo()
    {
        $this->queueProvider->updateQueueEntry('valid', 1, ['foo' => JobStatus::JOB_STATUS_FINISHED]);
    }

    public function testCleanUp()
    {
        $this->assertTrue(unlink(__DIR__ . '/../Entity/Valid.php'));
        $this->assertTrue(unlink(__DIR__ . '/../Entity/ValidTwo.php'));
        $this->assertTrue(unlink(__DIR__ . '/../Entity/ValidTwoHistory.php'));
    }

    /**
     * @return CreateDoctrineEntityProcess
     */
    protected function getEntityProcess()
    {
        return $this->kernel->getContainer()->get('simpleq.queue.create.process');
    }

    /**
     * @return Connection
     */
    protected function getDoctrine()
    {
        return $this->kernel->getContainer()->get('doctrine')->getConnection();
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        $time = new \DateTime();
        $timeFormat = $time->format('Y-m-d h:i:s');

        return $this->createArrayDataSet([
            'valid_' => [
                [
                    'id' => '1',
                    'task' => '',
                    'status' => 'open',
                    'data' => '{"test":"testval"}',
                    'created' => $timeFormat,
                    'updated' => $timeFormat
                ],
                [
                    'id' => '2',
                    'task' => 'test',
                    'status' => 'open',
                    'data' => '{"test":"testval2"}',
                    'created' => $timeFormat,
                    'updated' => $timeFormat
                ],
                [
                    'id' => '3',
                    'task' => 'test',
                    'status' => 'open',
                    'data' => '{"test":"testval2"}',
                    'created' => $timeFormat,
                    'updated' => $timeFormat
                ],
                [
                    'id' => '4',
                    'task' => 'test2',
                    'status' => 'failed',
                    'data' => '{"test":"testval2"}',
                    'created' => $timeFormat,
                    'updated' => $timeFormat
                ]
            ]
        ]);
    }
}
