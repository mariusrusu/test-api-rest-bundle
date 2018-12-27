<?php
namespace EveryCheck\TestApiRestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;

class AbstractBaseControllerTestClass extends WebTestCase
{
    protected $client;
    protected $application;
    protected $crawler;  
    static protected $fixtureFilename = null; 

    public function getClientOrCreateOne() 
    {
        if (null === $this->client) 
        {
            $this->client = static::createClient();
        }
        return $this->client;
    }

    public function getApplicationOrCreateOne() 
    {  
        if (null === $this->application) 
        {
            $this->application = new Application($this->getClientOrCreateOne()->getKernel());
            $this->application->setAutoExit(false);
        }
        return $this->application;
    }

    public function setUp($fixtureFilename = null)
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;

        $this->getClientOrCreateOne();
        $folder  = static::$kernel->getContainer()->getParameter('kernel.root_dir');
        $dbPath  = static::$kernel->getContainer()->getParameter('database_url');

        if(substr($dbPath,0,8) == 'mysql://')
        {
            $this->runCommand('d:database:import var/data/db_test/'.$fixtureFilename.'.sql');
        }
        else if(substr($dbPath,0,10) == 'sqlite:///')
        {
            $file_in  = $folder . '/../var/data/db_test/'.$fixtureFilename.'.sqlite';
            $file_out = substr($dbPath,10);
            copy($file_in,$file_out);
        }
        else
        {
            throw new \Exception("Bad test_database_url parameter", 1);
        }
    }

    protected function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);
        return $this->getApplicationOrCreateOne()->run(new StringInput($command));
    } 

    protected function runShellCOmmand($command){
        $output = [];
        $status = 1;
        exec($command .  ' 2>&1; echo $?',$output,$status);
        if($status != 0) var_dump($output);

    }  

    protected function getFixtureFullPath($fixtureFilename)
    {
        $calledClass = get_called_class();
        $calledClassFolder = dirname((new \ReflectionClass($calledClass))->getFileName());
        
        return join(DIRECTORY_SEPARATOR,[$calledClassFolder,'..', 'DataFixtures', 'ORM',$fixtureFilename . '.php']);
    }

}