<?php 

namespace TB\Bundle\FrontendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tb:search:index')
            ->setDescription('Indexes all entities a specified type')
            ->addArgument('type', InputArgument::REQUIRED, 'The type to index')
            ->addArgument('id', InputArgument::OPTIONAL, 'Optional a single object to index')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->client = $this->getContainer()->get('tb.elasticsearch.client');
        $type = $input->getArgument('type');
        $id = $input->getArgument('id');
        
        switch ($type) {
            case 'route':
                $this->indexRouteType($output, $id);
                break;
            case 'user_profile':
                $this->indexUserProfileType($output, $id);
                break;
            case 'brand_profile':
                $this->indexBrandProfileType($output, $id);
                break;                
            case 'event':
                $this->indexEventType($output, $id);
                break;
            case 'editorial':
                $this->indexEditorialType($output, $id);
                break;              
            case 'all':
                $this->indexRouteType($output);
                $this->indexUserProfileType($output);
                $this->indexBrandProfileType($output);
                $this->indexEventType($output);
                $this->indexEditorialType($output);
                break;                                    
            default:
                $output->writeln(sprintf('<error>Unknown type "%s"</error>', $type));
                break;
        }
    }
    
    protected function indexRouteType($output, $id = null)
    {
        if ($id == null) {
            $routes = $this->em->createQuery('
                    SELECT r FROM TBFrontendBundle:Route r
                    WHERE r.publish = true AND r.approved = true')
                ->getResult();
        } else {
            $routes = $this->em->createQuery('
                    SELECT r FROM TBFrontendBundle:Route r
                    WHERE r.publish = true AND r.approved = true
                    AND r.id = :id')
                ->setParameter('id', $id)
                ->getResult();
            
            if (count($routes) == 0) {
                $output->writeln(sprintf('<error>No Route found for specified id %s </error>', $id));
                exit;
            }
        }
        
        
        foreach ($routes as $route) {
            $suggestText = $route->getName() . ' ' . $route->getRegion();
                
            $doc = [
                'suggest_text' => $suggestText,
                'name' => $route->getName(),
                'short_name' => $route->getShortName(),
                'region' => $route->getRegion(),
                'slug' => $route->getSlug(),
            ];
            
            if ($route->getMedia()) {
                $doc['media'] = $route->getMedia()->getPath();
            }
                
            $params = [
                'body' => $doc,
                'index' => 'trailburning',
                'type' => 'route',
                'id' => $route->getId(),
            ];
            $this->client->index($params);
        }
        
        $output->writeln(sprintf('%s route(s) were indexed', count($routes)));
        $output->writeln('OK');
    }
    
    protected function indexUserProfileType($output, $id = null)
    {
        if ($id == null) {
            $users = $this->em->createQuery('
                    SELECT u FROM TBFrontendBundle:UserProfile u')
                ->getResult();            
        } else {
            $users = $this->em->createQuery('
                    SELECT u FROM TBFrontendBundle:UserProfile u
                    WHERE u.id = :id')
                ->setParameter('id', $id)
                ->getResult();
            if (count($users) == 0) {
                $output->writeln(sprintf('<error>No User found for specified id %s </error>', $id));
                exit;
            }
        }
        
        foreach ($users as $user) {
                
            $doc = [
                'suggest_text' => $user->getTitle(),
                'name' => $user->getName(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'avatar' => $user->getAvatarUrl(),
            ];
                
            $params = [
                'body' => $doc,
                'index' => 'trailburning',
                'type' => 'user_profile',
                'id' => $user->getId(),
            ];
            $this->client->index($params);
        }
        
        $output->writeln(sprintf('%s user(s) were indexed', count($users)));
        $output->writeln('OK');
    }
    
    protected function indexBrandProfileType($output, $id = null)
    {
        if ($id == null) {
            $brands = $this->em->createQuery('
                    SELECT b FROM TBFrontendBundle:BrandProfile b')
                ->getResult();            
        } else {
            $brands = $this->em->createQuery('
                    SELECT b FROM TBFrontendBundle:BrandProfile b
                    WHERE b.id = :id')
                ->setParameter('id', $id)
                ->getResult();
            if (count($brands) == 0) {
                $output->writeln(sprintf('<error>No Brand found for specified id %s </error>', $id));
                exit;
            }
        }
        
        foreach ($brands as $brand) {
                
            $doc = [
                'suggest_text' => $brand->getTitle(),
                'name' => $brand->getName(),
                'display_name' => $brand->getDisplayName(),
                'avatar' => $brand->getAvatarUrl(),
            ];
                
            $params = [
                'body' => $doc,
                'index' => 'trailburning',
                'type' => 'brand_profile',
                'id' => $brand->getId(),
            ];
            $this->client->index($params);
        }
        
        $output->writeln(sprintf('%s brand(s) were indexed', count($brands)));
        $output->writeln('OK');
    }
    
    protected function indexEventType($output, $id = null)
    {
        if ($id == null) {
            $events = $this->em->createQuery('
                    SELECT e FROM TBFrontendBundle:Event e')
                ->getResult();            
        } else {
            $events = $this->em->createQuery('
                    SELECT e FROM TBFrontendBundle:Event e
                    WHERE e.id = :id')
                ->setParameter('id', $id)
                ->getResult();
            if (count($events) == 0) {
                $output->writeln(sprintf('<error>No Event found for specified id %s </error>', $id));
                exit;
            }                
        }
        
        foreach ($events as $event) {
            $suggestText = $event->getTitle() . ' ' . $event->getTitle2();
            
            $doc = [
                'suggest_text' => $suggestText,
                'title' => $event->getTitle(),
                'title2' => $event->getTitle2(),
                'slug' => $event->getSlug(),
                'logo_small' => $event->getLogoSmall(),
            ];

            $params = [
                'body' => $doc,
                'index' => 'trailburning',
                'type' => 'event',
                'id' => $event->getId(),
            ];
            $this->client->index($params);
        }
        
        $output->writeln(sprintf('%s event(s) were indexed', count($events)));
        $output->writeln('OK');
    }
    
    protected function indexEditorialType($output, $id = null)
    {
        if ($id == null) {
            $editorials = $this->em->createQuery('
                    SELECT e FROM TBFrontendBundle:Editorial e')
                ->getResult();            
        } else {
            $editorials = $this->em->createQuery('
                    SELECT e FROM TBFrontendBundle:Editorial e
                    WHERE e.id = :id')
                ->setParameter('id', $id)
                ->getResult();
            if (count($editorials) == 0) {
                $output->writeln(sprintf('<error>No Editorial found for specified id %s </error>', $id));
                exit;
            }                
        }
        
        foreach ($editorials as $editorial) {
            $title = preg_replace('/(\r?\n){2,}/', ' ', $editorial->getTitle());
            $title = trim(preg_replace('/\s+/', ' ', $title));
                
            $doc = [
                'suggest_text' => $title,
                'title' => $title,
                'slug' => $editorial->getSlug(),
                'image' => $editorial->getImage(),
            ];

            $params = [
                'body' => $doc,
                'index' => 'trailburning',
                'type' => 'editorial',
                'id' => $editorial->getId(),
            ];
            $this->client->index($params);
        }
        
        $output->writeln(sprintf('%s editorial(s) were indexed', count($editorials)));
        $output->writeln('OK');
    }
}