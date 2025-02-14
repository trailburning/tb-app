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
            ->setDescription('Finds all media files with double references in the database and creates unique files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = $this->getContainer()->get('trail_media_files_filesystem');
        $adapter = $filesystem->getAdapter();
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $query = $em->createQuery('SELECT m.path FROM TBFrontendBundle:Media m GROUP BY m.path HAVING COUNT(m.id) > 1');
        $mediaQuery = $em->createQuery('SELECT m FROM TBFrontendBundle:Media m WHERE m.path = :path');
        
        foreach ($query->getResult() as $result) {
            $medias = $mediaQuery->setParameter('path', $result['path'])->getResult();
            $oldPath = $result['path'];
            foreach ($medias as $media) {    
                $newPath = '/' . $media->getId() . $oldPath;
                $adapter->setMetadata($shareImageFilepath, array('ContentType' => 'image/jpeg', 'ACL' => 'public-read'));
                $adapter->write($newPath, $filesystem->read($oldPath));
                $media->setPath($newPath);
                $em->persist($media);
                $em->flush();
            }
        }  
        
        
    }
    
}