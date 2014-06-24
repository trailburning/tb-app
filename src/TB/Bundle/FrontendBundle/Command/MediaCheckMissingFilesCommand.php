<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MediaCheckMissingFilesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:media:check-missing')
            ->setDescription('Checks for missing media files.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = $this->getContainer()->get('trail_media_files_filesystem');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $missingCount = 0;
        $query = $em->createQuery('
                SELECT m.path FROM TBFrontendBundle:Media m
            ');
        
        foreach ($query->getResult() as $media) {
            if (!$filesystem->has($media['path'])) {
                $output->writeln('missing: ' . $media['path']);
                $missingCount++;
            }
        }
        
        $output->writeln(sprintf('missing %s media', $missingCount));
    }
    
   
}