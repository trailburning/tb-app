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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->client = $this->getContainer()->get('tb.elasticsearch.client');
        $type = $input->getArgument('type');
        
        switch ($type) {
            case 'route':
                $this->initRouteType();
                break;
            case 'user_profile':
                $this->initUserProfileType();
                break;                
            default:
                $output->writeln(sprintf('<error>Unknown type "%s"</error>', $type));
                break;
        }
    }
    
    protected function initRouteType()
    {
        // Create the new index 
        
        $routes = $this->em->createQuery('
                SELECT r FROM TBFrontendBundle:Route r
                WHERE r.publish = true AND r.approved = true')
            ->getResult();
        
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
    }
    
    protected function initUserProfileType()
    {
        // Create the new index 
        
        $users = $this->em->createQuery('
                SELECT u FROM TBFrontendBundle:UserProfile u
            ')
            ->getResult();
        
        foreach ($users as $user) {
                
            $doc = [
                'suggest_text' => $user->getTitle(),
                'name' => $user->getName(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'location' => $user->getLocation(),
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
    }
}