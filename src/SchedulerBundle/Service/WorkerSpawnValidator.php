<?php

namespace simpleq\SchedulerBundle\Service;

class WorkerSpawnValidator
{

    const MAX_USED_MEMORY_IN_PERCENT = 90;

    /**
     * @var WorkerProvider
     */
    protected $workerProvider;

    /**
     * @var string
     */
    protected $validationFailure;

    /**
     * WorkerSpawnValidator constructor.
     * @param WorkerProvider $workerProvider
     */
    public function __construct(WorkerProvider $workerProvider)
    {
        $this->workerProvider = $workerProvider;
    }

    /**
     * @param array $worker
     * @return bool|string
     * @throws \Exception
     */
    public function validate(array $worker)
    {
        if (
            $this->validateLimit($worker)
            && $this->validateLoad($worker)
            && $this->validateMemory($worker)
        ) {
            return true;
        }

        return $this->validationFailure;
    }

    /**
     * @param array $worker
     * @return bool|string
     */
    protected function validateMemory(array $worker)
    {
        $data    = explode("\n", shell_exec("/proc/meminfo"));
        $memInfo = [];
        foreach ($data as $line) {
            list($key, $val) = explode(":", $line);
            $memInfo[$key] = trim($val);
        }

        $memoryUsedKB = memory_get_usage(true);
        $memTotal     = intval($memInfo['MemTotal']);
        $memFree      = intval($memInfo['MemFree']);

        if (
            $memoryUsedKB >= $this->convertShortTermNumber($this->workerProvider->getWorkerMaxMemory($worker['class']))
            || $memoryUsedKB / 1024 >= ($memTotal / 100) * self::MAX_USED_MEMORY_IN_PERCENT
            || $memFree <= ($memTotal / 100) * (100 - self::MAX_USED_MEMORY_IN_PERCENT)
        ) {
            $this->validationFailure = 'Memory usage is to high, stop spawning workers';

            return false;
        }

        return true;
    }

    /**
     * @param array $worker
     * @return bool|string
     */
    protected function validateLoad(array $worker)
    {
        $load    = sys_getloadavg();
        $maxLoad = $this->workerProvider->getWorkerMaxLoad($worker['class']);
        if ($maxLoad > 0 && $maxLoad <= $load[0]) {
            $this->validationFailure = sprintf('Max. server load reached for service %s', $worker['class']);

            return false;
        }

        return true;
    }

    /**
     * @param array $worker
     * @return bool|string
     * @throws \Exception
     */
    protected function validateLimit(array $worker)
    {
        try {
            if ($this->isWorkerLimitReached($worker)) {
                $this->validationFailure = sprintf('Limit reached for service %s', $worker['class']);

                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception(
                'Could not connect to WorkingQueue. Dont forget to run simpleq:scheduler:init first'
            );
        }

        return true;
    }

    /**
     * @param array $worker
     * @return bool
     */
    protected function isWorkerLimitReached(array $worker)
    {
        if (!isset($worker['limit']) || $worker['limit'] === 0) {
            return false;
        }

        $limit = $worker['limit'];

        return $this->workerProvider->getActiveWorkerCount($worker['class']) >= $limit;
    }

    /**
     * @param string $shortTermValue
     * @return int
     */
    protected function convertShortTermNumber($shortTermValue)
    {
        $shortTerms = [
            'K'  => 1024,
            'KB' => 1024,
            'M'  => 1024 * 1024,
            'MB' => 1024 * 1024,
            'G'  => 1024 * 1024 * 1024,
            'GB' => 1024 * 1024 * 1024,
        ];

        foreach ($shortTerms as $shortTermKey => $shortTermMultiplier) {
            if (strpos($shortTermValue, $shortTermKey) !== false) {
                return intval($shortTermValue) * $shortTermMultiplier;
            }
        }

        return intval($shortTermValue);
    }
}