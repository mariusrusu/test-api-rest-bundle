<?php
namespace EveryCheck\TestApiRestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;

class PrepareDatabaseForTestCommand extends ContainerAwareCommand
{

    const DEFAULT_DUMPED_DATABASE_LOCATION = '../var/data/db_test/';

    protected function configure()
    {

        $this
            ->setName('test:database:build-one')
            ->setDescription('Greet someone')
            ->addArgument(
                'fixture',
                InputArgument::REQUIRED,
                'Fixture use to fill database. See test:fixture:list to see which fixture is available.'
            )
            ->addOption(
                'path',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, the task will yell in uppercase letters',
                self::DEFAULT_DUMPED_DATABASE_LOCATION
            )
            ->addOption(
                'query',
                null,
                InputOption::VALUE_OPTIONAL,
                'Xpath query to locate fixture file in tests folders:',
                ListAllTestFixtureAvailableCommand::DEFAULT_XPATH_QUERY
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    { 
        $this->outputInterface = $output;

        $root  = $this->getContainer()->getParameter('kernel.root_dir');
        $path  = $root . '/'. $input->getOption('path');  
        $query  = $root . '/'. $input->getOption('query');  
        $fixture =  $input->getArgument('fixture');   

        $files = $this->findAllFixtureFileInTestFolder($query);

        if (array_key_exists($fixture,$files) === false )
        {   
            $output->writeLn('<error>fixture :'.$fixture.' does not exist.</error>');
            return ;
        }

        $this->prepareDatabaseForFixtureSavedAtPath($files[$fixture],$path.$fixture);

    }

    protected function findAllFixtureFileInTestFolder($query)
    {   
        $output =[] ;
        foreach (glob($query) as $filename) {
            $output[ basename($filename,'.php') ] = $filename;
        }
        return $output;
    }

    function prepareDatabaseForFixtureSavedAtPath($fixture,$path)
    {
        $dbPath  = $this->getContainer()->getParameter('database_url');

        if(substr($dbPath,0,8) == 'mysql://')
        {           
            $this->outputInterface->writeLn('<comment>mysql detected</comment>');
            $this->callingCommand('d:d:d',['--force'=>true]);
            $this->callingCommand('d:d:c');
            $this->callingCommand('d:m:m',['--no-interaction' => true]);
            $this->callingCommand('d:f:l',['--append'=>true,'--fixtures'=>$fixture,'-v'=>true]);
            
            $this->dumpDatabase($dbPath,$path.'.sql');
        }
        else if(substr($dbPath,0,10) == 'sqlite:///')
        {
            $this->outputInterface->writeLn('<comment>sqlite detected</comment>');
            $this->callingCommand('d:d:d',['--force'=>true]);
            $this->callingCommand('d:d:c');
            $this->callingCommand('d:s:c');
            $this->callingCommand('d:m:m',['--no-interaction' => true]);
            $this->callingCommand('d:f:l',['--append'=>true,'--fixtures'=>$fixture,'-v'=>true]);
        
            $sqlitePath = substr($dbPath,10);
            rename($sqlitePath ,$path. '.sqlite');
        }
        else
        {
            $this->outputInterface->writeLn('<error>test_database_url : bad configuration must be mysql path or a path to .sqlite file.</error>');
        }

    }

    function dumpDatabase($mysqlPath, $path)
    {
        $mysqlPathPCRE = '/^mysql:\/\/(.+):(.+)@(.+):(\d{2,5})\/(.+)$/m';

        preg_match_all($mysqlPathPCRE, $mysqlPath, $matches, PREG_SET_ORDER, 0);

        if(count($matches) != 1 || count($matches[0]) != 6 )
        {
            $this->outputInterface->writeLn('<error>test_database_url : cannot parse it properly, may be you forget to specify the port ?.</error>');
            return;
        }

        $user     = $matches[0][1];
        $password = $matches[0][2];
        $host     = $matches[0][3];
        $port     = $matches[0][4];
        $name     = $matches[0][5];

        if (`which mysqldump`) 
        {
            $command  ="mysqldump  -h $host -P $port --databases $name --user=$user --password=$password > $path ";
            $this->outputInterface->writeLn('<command>Executing : '.$command.'</command>');

            $output = shell_exec($command);
            $this->outputInterface->writeLn($output);
        }

    }

    function callingCommand($command,$arguments=[],$interactive = false)
    {        
        $runnableCommand = null;
        try{
            $runnableCommand = $this->getApplication()->find($command);
        }
        catch(\Symfony\Component\Console\Exception\CommandNotFoundException $e)
        {
            $this->outputInterface->writeLn('<info>Executing : command \"'.$command.'\" not found, skipped</info>');
            return false;
        }

        $outputStyle = new OutputFormatterStyle('white', 'magenta', array('bold'));
        $this->outputInterface->getFormatter()->setStyle('command', $outputStyle);

        $this->tmp = '';
        array_walk($arguments,  [$this, 'concatInTmp'] );
        $this->outputInterface->writeLn('<command>Executing : '.$command.$this->tmp.'</command>');

        $arguments['command'] = $command;
        $input = new ArrayInput($arguments);
        $input->setInteractive($interactive);
        return $runnableCommand->run($input,$this->outputInterface);
    }


    private function concatInTmp($item, $key)
    {
        $this->tmp = $this->tmp . ' ' . $key .'='.$item;
    }
}