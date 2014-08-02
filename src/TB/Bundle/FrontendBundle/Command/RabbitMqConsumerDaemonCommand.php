<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use React\EventLoop\Factory;
use React\ChildProcess\Process;

class RabbitMqConsumerDaemonCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:consumer:daemon')
            ->setDescription('Starts multiple RabbitMQ consumer in a event loop')
            ->addOption('consumer-count', 'c', InputOption::VALUE_OPTIONAL, 'Count of the consumer to start, default is 3', 3)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consumerCount = $input->getOption('consumer-count');
        $processes = [];
        
        $loop = Factory::create();
        
        for ($i=0; $i < $consumerCount; $i++) { 
            $process = new Process('php -dmemory_limit=256M app/console rabbitmq:consumer main');
            $process->start($loop);
            $processes[] = $process;
        }
        
        // $loop->addPeriodicTimer(1, function($timer) use (&$processes) {
        //     foreach ($processes as &$process) {
        //         if ($process->isRunning()) {
        //             echo sprintf("Process with pid %s is running\n", $process->getPid());
        //         } else {
        //             $process->start($timer->getLoop());
        //             echo sprintf("Starting process with pid %s\n", $process->getPid());
        //         }
        //     }
        // });
        //
        // $loop->addPeriodicTimer(2, function($timer) use (&$processes) {
        //     foreach ($processes as &$process) {
        //         if ($process->isRunning()) {
        //             $process->close();
        //
        //             echo sprintf("Terminating process with pid %s, exit code %s, term signal %s\n", $process->getPid());
        //         }
        //     }
        // });
        
        $loop->run();
    }
}