<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MediaDeleteUnusedCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:media:unused:delete')
            ->setDescription('Deleted unused media files.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = $this->getContainer()->get('trail_media_files_filesystem');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $unusedCount = 0;
        $usedCount = 0;
        $duplicatedCount = 0;
        
        foreach ($filesystem->keys() as $path) {
            
            if (strpos($path, '_share') === false) {
                $query = $em
                    ->createQuery('
                        SELECT m FROM TBFrontendBundle:Media m
                        WHERE m.path = :path')
                    ->setParameter('path', '/' . $path); 
            } else {
                $query = $em
                    ->createQuery('
                        SELECT m FROM TBFrontendBundle:Media m
                        WHERE m.sharePath = :path')
                    ->setParameter('path', '/' . $path);
            }
            
            try {
                $media = $query->getSingleResult();
                $usedCount++;
            } catch (\Doctrine\ORM\NoResultException $e) {
                $unusedCount++;
                $output->writeln(sprintf('delete file in path: %s', $path));
                $filesystem->delete($path);
            } catch (\Doctrine\ORM\NonUniqueResultException $e) {
                $duplicatedCount++;
                $output->writeln(sprintf('duplicated reference for path: %s', $path));
            }
        }
        $output->writeln(sprintf('found %s used media, deleted: %s unused media, found %s duplicated references', $usedCount, $unusedCount, $duplicatedCount));
    }
    
   
}