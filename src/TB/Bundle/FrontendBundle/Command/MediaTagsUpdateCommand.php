<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

class MediaTagsUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:media:tags:update')
            ->setDescription('Updates the image media size')
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'The id of the Media to update', null)
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The path of the Media to update', null)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mediaImporter = $this->getContainer()->get('tb.media.importer');
        
        if ($input->getOption('id') !== null) {
            $medias = $em->getRepository('TBFrontendBundle:Media')->findBy([
                'id' => $input->getOption('id')
            ]);
        } elseif ($input->getOption('path') !== null) {
            $medias = $em->getRepository('TBFrontendBundle:Media')->findBy([
                'path' => $input->getOption('path')
            ]);
        } else {
            $medias = $em->getRepository('TBFrontendBundle:Media')->findAll();
        }
        
        
        foreach ($medias as $media) {
            $file = new File('http://media.trailburning.com' . $media->getPath(), false);
            $media->setFile($file);
            $media->readMetadata($mediaImporter);
            $em->persist($media);
        }
        $em->flush();
    }
}