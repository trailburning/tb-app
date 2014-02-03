<?php

namespace Codeception\Module;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Symfony\Bundle\DoctrineFixturesBundle\Common\DataFixtures\Loader as SymfonyFixturesLoader;
use Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader as DoctrineFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;


// here you can define custom functions for TestGuy 

class TestHelper extends \Codeception\Module
{
    public static $em = null;
    
    protected $metadatas;
    
    protected $schemaTool;
    
    protected $populated = false;
    
    /**
     * Get the Doctrine Entity Manager from the Symfony Module, load the Schema Metadata and setup the Database
     */
    public function _beforeSuite($settings = array()) {
    
        // get the symfony kernel from the symfony module and boot
        
        if (!$this->hasModule('Symfony2')) {
            throw new \Exception('Module Symfony2 is required');
        }
        
        if (self::$em === null) {
            $kernel = $this->getModule('Symfony2')->kernel;
            $kernel->boot();
            self::$em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        }
        
        $this->schemaTool = new SchemaTool(self::$em);
        $this->metadatas = self::$em->getMetadataFactory()->getAllMetadata(); 
        
        $this->cleanup();
        $this->loadSchema();
        $this->populated = true;
        
        parent::_beforeSuite($settings);

    }
    
    /**
     * Drops and Creates the schema before each test
     */
    public function _before(\Codeception\TestCase $test)
    {
        if (!$this->populated) {
            $this->cleanup();
            $this->loadSchema();
        }
        parent::_before($test);
    }

    public function _after(\Codeception\TestCase $test)
    {
        $this->populated = false;
    }
    
    /**
     * Creates all Entities defined by this Bundle Entities
     */
    protected function loadSchema() {
        //$this->schemaTool->createSchema($this->metadatas);
    }
    
    /**
     * Deletes all tables defined by this Bundle Entities 
     */
    protected function cleanup() {
        //$this->schemaTool->dropSchema($this->metadatas);
    }
    
    public function loadFixtures(array $classNames, $purgeMode = null)
    {
        $container = $this->getModule('Symfony2')->kernel->getContainer();

        $executorClass = 'Doctrine\\Common\\DataFixtures\\Executor\\ORMExecutor';
        $referenceRepository = new ProxyReferenceRepository(self::$em);
        $cacheDriver = self::$em->getMetadataFactory()->getCacheDriver();

        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }
    
        $purgerClass = 'Doctrine\\Common\\DataFixtures\\Purger\\ORMPurger';
        $purger = new $purgerClass();
        if (null !== $purgeMode) {
            $purger->setPurgeMode($purgeMode);
        }

        $executor = new $executorClass(self::$em/*, $purger*/);
        $executor->setReferenceRepository($referenceRepository);
        //$executor->purge();
    
        $loader = $this->getFixtureLoader($container, $classNames);

        $executor->execute($loader->getFixtures(), true);

        if (isset($name) && isset($backup)) {
            $executor->getReferenceRepository()->save($backup);
            copy($name, $backup);
        }

        return $executor;
    }
    
    protected function getFixtureLoader(ContainerInterface $container, array $classNames)
    {
        $loader = class_exists('Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader')
            ? new DataFixturesLoader($container)
            : (class_exists('Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader')
                ? new DoctrineFixturesLoader($container)
                : new SymfonyFixturesLoader($container));

        foreach ($classNames as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        return $loader;
    }
    
    protected function loadFixtureClass($loader, $className)
    {   
        $fixture = new $className();
        $fixture->load(self::$em);
        exit;
        if ($loader->hasFixture($fixture)) {
            unset($fixture);
            return;
        }

        $loader->addFixture($fixture);

        if ($fixture instanceof DependentFixtureInterface) {
            foreach ($fixture->getDependencies() as $dependency) {
                $this->loadFixtureClass($loader, $dependency);
            }
        }
    }
    
    /**
     * Loads given DataFixtures
     */
    public function loadFixturesMy(array $fixtureNames)
    {
        $container = $this->getModule('Symfony2')->kernel->getContainer();
        
        $loader = new DataFixturesLoader($container);

        $fixtures = array();
        $includedFiles = array();

        $dir = realpath(__DIR__ . '/../../DataFixtures/ORM');
        
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('"%s" does not exist', $dir));
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($fixtureNames as $fixtureName) {
            $sourceFile = $dir . '/' . $fixtureName . '.php';
            require_once $sourceFile;
            $includedFiles[] = $sourceFile;
        }
        
        $declared = get_declared_classes();
        
        foreach ($declared as $className) {
            $reflClass = new \ReflectionClass($className);
            $sourceFile = $reflClass->getFileName();
            
            if (in_array($sourceFile, $includedFiles)) {
                $fixture = new $className;
                $loader->addFixture($fixture);
            }
        }
        
        $fixtures = $loader->getFixtures();
        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('No Fixtures were loaded for: %s', "\n\n- ".implode("\n- ", $fixtureNames))
            );
        }
        $purger = new ORMPurger(self::$em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $executor = new ORMExecutor(self::$em/*, $purger*/);
        $executor->execute($fixtures, false);
    }
        
}
