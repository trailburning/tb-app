<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MediaUnusedStatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:media:unused')
            ->setDescription('Finds all media files not referenced in the database and outputs the total file size.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = $this->getContainer()->get('trail_media_files_filesystem');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $unusedCount = 0;
        $usedCount = 0;
        $totalUnusedFilesize = 0;
        $totalUsedFilesize = 0;
        
        foreach ($filesystem->keys() as $path) {
            
            if (strpos($path, '_share') === false) {
                $query = $em
                    ->createQuery('
                        SELECT m FROM TBFrontendBundle:Media m
                        WHERE m.path = :path 
                        OR m.path = :altpath')
                    ->setParameter('path', '/' . $path)    
                    ->setParameter('altpath', 'trailburning-media/' . $path); 
            } else {
                $query = $em
                    ->createQuery('
                        SELECT m FROM TBFrontendBundle:Media m
                        WHERE m.sharePath = :path 
                        OR m.sharePath = :altpath')
                    ->setParameter('path', '/' . $path)    
                    ->setParameter('altpath', 'trailburning-media/' . $path);
            }
            
            try {
                $media = $query->getSingleResult();
                $usedCount++;
                $totalUsedFilesize += $media->getTags()['filesize'];
            } catch (\Doctrine\ORM\NoResultException $e) {
                $unusedCount++;
                $totalUnusedFilesize += $this->getFileSize($path);
                // $totalUnusedFilesize += $filesystem->size($path);
            } catch (\Doctrine\ORM\NonUniqueResultException $e) {
                $usedCount++;
                $totalUnusedFilesize += $this->getFileSize($path);
                // $totalUsedFilesize += $filesystem->size($path);
            }
        }
        $output->writeln(sprintf('used: %s (%s MB), unused: %s (%s MB)', $usedCount, round(($totalUsedFilesize / 1024 / 1024), 2), $unusedCount, round(($totalUnusedFilesize / 1024 / 1024), 2)));
    }
    
    public function getFileSize($path)
    {
        if (strpos($path, 'trailburning-media') === false) {
            $url = 'http://s3-eu-west-1.amazonaws.com/trailburning-media/' . $path;
        } else {
            $url = 'http://s3-eu-west-1.amazonaws.com/' . $path;
        }
        // Assume failure.
        $result = -1;

        $curl = curl_init($url);

        // Issue a HEAD request and follow any redirects.
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_USERAGENT, '');

        $data = curl_exec($curl);
        curl_close($curl);

        if ($data) {
            $content_length = "unknown";
            $status = "unknown";

            if (preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches)) {
                $status = (int)$matches[1];
            }

            if (preg_match("/Content-Length: (\d+)/", $data, $matches)) {
                $content_length = (int)$matches[1];
            }

            // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
            if($status == 200 || ($status > 300 && $status <= 308)) {
                $result = $content_length;
            }
        }
        
        return $result;
    }
}