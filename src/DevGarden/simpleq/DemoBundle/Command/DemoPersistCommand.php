<?php

namespace DevGarden\simpleq\DemoBundle\Command;

use DevGarden\simpleq\QueueBundle\Entity\Dummy;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DemoPersistCommand extends ContainerAwareCommand
{
    public function configure(){
        $this->setName('simpleq:demo:persist');
        $this->addArgument('times', InputArgument::OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output){
        $times = ($input->getArgument('times')) ? $input->getArgument('times') : 1;
        for ($i=0; $i<$times; $i++){
            $output->writeln('Persist Demo Task');
            try{
                $this->demoPersist();
            } catch (\Exception $e) {
                $output->writeln('Error => '.$e->getMessage());
            }
        }
    }

    public function demoPersist(){
        $product = new Dummy();
        $product->setTask('test');
        $product->setStatus('open');
        $product->setCreated(new \DateTime());
        $product->setUpdated(new \DateTime());
        $this->getContainer()->get('doctrine')->getManager()->persist($product);
        $this->getContainer()->get('doctrine')->getManager()->flush();
    }
}