<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RouteCreateShareImageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:route:create-share-image')
            ->setDescription('Create the Routes share image')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the Route')
            ->addOption('fault-tolerant', 'f', InputOption::VALUE_OPTIONAL, 'If set to true, no exception is thrown', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $faultTolerant = $input->getOption('consumer-count');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $route = $em->getRepository('TBFrontendBundle:Route')->findOneById($id);
        if (!$route) {
            if ($faultTolerant === false) {
                throw new \Exception(sprintf('Route with id %s not found', $id));
            } else {
                $output->writeln(sprintf('Route with id %s not found', $id));
                $output->writeln('OK');
                
                return true;
            }
        }
        
        $imageGenerator = $this->getContainer()->get('tb.image.generator');   
        $imageGenerator->createRouteShareImage($route);

        $output->writeln('OK');  // Don't change the output, it would break the RabbitMQ worker
    }
}