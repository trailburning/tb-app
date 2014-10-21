<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BrandProfileCreateShareImageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:brand:create-share-image')
            ->setDescription('Create the BrandProfile share image')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the BrandProfile')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $event = $em->getRepository('TBFrontendBundle:BrandProfile')->findOneById($id);
        if (!$event) {
            throw new \Exception(sprintf('BrandProfile with id %s not found', $id));
        }
        
        $imageGenerator = $this->getContainer()->get('tb.image.generator');   
        $imageGenerator->createBrandProfileShareImage($event);

        $output->writeln('OK'); // Don't change the output, it would break the RabbitMQ worker
    }
}