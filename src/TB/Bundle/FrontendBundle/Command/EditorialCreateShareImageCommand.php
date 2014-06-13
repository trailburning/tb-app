<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EditorialCreateShareImageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:editorial:create-share-image')
            ->setDescription('Create the Editorial share images')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the Route')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $editorial = $em->getRepository('TBFrontendBundle:Editorial')->findOneById($id);
        if (!$editorial) {
            throw new \Exception(sprintf('Editorial with id %s not found', $id));
        }
        
        $imageGenerator = $this->getContainer()->get('tb.image.generator');   
        $imageGenerator->createEditorialShareImage($editorial);

        $output->writeln('OK');
    }
}