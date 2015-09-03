<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

use CommandPatterns;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

use DevGarden\simpleq\SchedulerBundle\Service\WorkerProvider;
use DevGarden\simpleq\QueueBundle\Service\QueueProvider;
use DevGarden\simpleq\QueueBundle\Service\JobProvider;
use DevGarden\simpleq\QueueBundle\Service\JobQueueHistoryProvider;
use DevGarden\simpleq\SchedulerBundle\Service\WorkingQueueHistoryProvider;
use DevGarden\simpleq\QueueBundle\Process\CreateDoctrineEntityProcess;

class BaseCommand extends ContainerAwareCommand
{

}