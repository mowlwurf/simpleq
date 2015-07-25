<?php

namespace DevGarden\simpleq\DemoBundle\Command;

use DevGarden\simpleq\QueueBundle\Entity\Dummy;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DemoPersistCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('simpleq:demo:persist');
        $this->addArgument('times', InputArgument::OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $times = ($input->getArgument('times')) ? $input->getArgument('times') : 1;
        $data = $this->getDemoData(ceil($times/10));
        for ($i = 1; $i < $times; $i++) {
            $output->writeln('Persist Demo Task');
            try {
                $this->demoPersist(['url' => $data[0][1][$i]]);
            } catch (\Exception $e) {
                $output->writeln('Error => ' . $e->getMessage());
            }
        }
    }

    /**
     * url to call http://creepycandids.tumblr.com/
     * use regexp blog-title-wrapper content.*src="(.*)"\sa to get image url
     * http://creepycandids.tumblr.com/page/n (10 images per page)
     * prepare downloadJobContainer array
     * @param int $n amount jobs ordered
     * @return array
     */
    protected function getDemoData($n){
        $jobData = [];
        for($i=1;$i<=$n;$i++){
            if ($i=1) {
                $url = 'http://creepycandids.tumblr.com/';
            } else {
                $url = sprintf('http://creepycandids.tumblr.com/page/%d',$i);
            }
            $sh = file_get_contents($url);
            if (preg_match_all('/.*src="(.*)"\sdata-width-lq/i', $sh, $result)) {
                unset($result[0]);
                array_push($jobData,$result);
            }
        }
        return $jobData;
    }

    /**
     * @param array $data
     */
    public function demoPersist(array $data)
    {
        $job = new Dummy();
        $job->setTask('download');
        $job->setStatus('open');
        $job->setData(json_encode($data));
        $job->setCreated(new \DateTime());
        $job->setUpdated(new \DateTime());
        $this->getContainer()->get('doctrine')->getManager()->persist($job);
        $this->getContainer()->get('doctrine')->getManager()->flush();
    }
}