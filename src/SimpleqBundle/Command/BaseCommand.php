<?php

namespace simpleq\SimpleqBundle\Command;

use CommandPatterns;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

namespace simpleq\SchedulerBundle\Service\WorkerProvider;
namespace simpleq\QueueBundle\Service\QueueProvider;
namespace simpleq\QueueBundle\Service\JobProvider;
namespace simpleq\QueueBundle\Service\JobQueueHistoryProvider;
namespace simpleq\SchedulerBundle\Service\WorkingQueueHistoryProvider;
namespace simpleq\QueueBundle\Process\CreateDoctrineEntityProcess;

class BaseCommand extends ContainerAwareCommand
{

}