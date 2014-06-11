<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MediaFixContentTypeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:media:fix-content-type')
            ->setDescription('Deleted unused media files.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = $this->getContainer()->get('trail_media_files_filesystem');
        $adapter = $filesystem->getAdapter();
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $missingCount = 0;
        $query = $em->createQuery('
                SELECT m.path FROM TBFrontendBundle:Media m
            ');
        
        foreach ($query->getResult() as $media) {
            $adapter->setMetadata('ContentType' => 'image/jpeg', 'ACL' => 'public-read');
        }
        
    }
    
   
}