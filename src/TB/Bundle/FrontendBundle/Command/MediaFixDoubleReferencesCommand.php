<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MediaFixDoubleReferencesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:media:fix-double-references')
            ->setDescription('Finds all media files not referenced in the database and outputs the total file size.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = $this->getContainer()->get('trail_media_files_filesystem');
        $adapter = $filesystem->getAdapter();
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $query = $em->createQuery('SELECT m FROM TBFrontendBundle:Media m');
            
        foreach ($query->getResult() as $media) {    
            $oldPath = $media->getPath();
            if (preg_match('/[^\/]+$/', $oldPath, $match)) {
                $newPath = sprintf('/%s/%s', $media->getRouteId(), $match[0]);
            } else {
                throw new Exception('unable to parse path');
            }
            
            if ($newPath != $oldPath) {
                $adapter->setMetadata($shareImageFilepath, array('ContentType' => 'image/jpeg', 'ACL' => 'public-read'));
                $adapter->write($newPath, $filesystem->read($oldPath));
                $media->setPath($newPath);
                $em->persist($media);
                $em->flush();
            }
            
        }
        
    }
    
}