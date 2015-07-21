<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

use DevGarden\simpleq\QueueBundle\EventListener\MappingListener;
use DevGarden\simpleq\QueueBundle\Service\QueueProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class QueueGenerateCommand extends ContainerAwareCommand
{

    public function configure(){
        $this->setName('simpleq:queue:create')
            ->addArgument('name', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output){
        $name = $input->getArgument('name');
        if (true === preg_match('/[\W\d]./', $name)) {
            throw new InvalidArgumentException('Input argument value contains invalid chars. Only [a-zA-Z_] are accepted');
        }
        try{
            $output->writeln(sprintf('Creating queue %s ...', $name));
            $queueProvider = $this->getQueueProvider();
            $queueProvider->generateQueue($name);
            $output->writeln('Updating schema ...');
            $command = $this->getApplication()->find('doctrine:schema:update');

            $arguments = array(
                'command' => 'doctrine:schema:update',
                '--force'  => true,
            );

            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $output);

        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
        return false;
    }

    /**
     * @param $name
     */
    protected function registerDoctrineEvent($name){
        $mappingListener = new MappingListener($name);
        $evm = $this->getContainer()->get('doctrine')->getManager()->getEventManager();
        $evm->addEventListener('loadClassMetadata', $mappingListener);
    }

    /**
     * @return QueueProvider
     */
    protected function getQueueProvider(){
        return $this->getContainer()->get('simpleq.queue.provider');
    }
}