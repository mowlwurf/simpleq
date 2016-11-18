<?php

namespace simpleq\SimpleqBundle\Command;

use simpleq\QueueBundle\Service\QueueProvider;
use simpleq\SimpleqBundle\Extension\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueGenerateCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this->setName(Command::QUEUE_GENERATE)
            ->addArgument('name', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws \InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        if (true === preg_match('/[\W\d]./', $name)) {
            throw new \InvalidArgumentException('Input argument value contains invalid chars. Only [a-zA-Z_] are accepted');
        }
        try {
            $output->writeln(sprintf('Creating queue %s ...', $name));
            $queueProvider = $this->getQueueProvider();
            $queueProvider->generateQueue($name);
            $output->writeln('Updating schema ...');
            $command = $this->getApplication()->find('doctrine:schema:update');

            $arguments = array(
                'command' => 'doctrine:schema:update',
                '--force' => true,
            );

            $input      = new ArrayInput($arguments);
            $returnCode = $command->run($input, $output);

        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

        return false;
    }

    /**
     * @return QueueProvider
     */
    protected function getQueueProvider()
    {
        return $this->getContainer()->get('simpleq.queue.provider');
    }
}