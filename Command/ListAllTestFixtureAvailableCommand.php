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

class ListAllTestFixtureAvailableCommand extends ContainerAwareCommand
{

    const DEFAULT_XPATH_QUERY = '../tests/*Bundle/DataFixtures/ORM/*Fixture.php';

    protected function configure()
    {
        $this
            ->setName('test:fixture:list')
            ->setDescription('List all fixture available for testing')
            ->setDefinition(
                new InputDefinition([
                    new InputOption(
                        'query',
                        'x',
                        InputOption::VALUE_OPTIONAL,
                        'Xpath query to locate fixture file in tests folders:',
                        self::DEFAULT_XPATH_QUERY
                    )
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    { 
        $root  = $this->getContainer()->getParameter('kernel.root_dir');
        $query = $root . '/'. $input->getOption('query');     

        $files = $this->findAllFixtureFileInTestFolder($query);
        $output->writeLn(implode(' ',$files));
    }

    protected function findAllFixtureFileInTestFolder($query)
    {   
        $output =[] ;
        foreach (glob($query) as $filename)
        {
            array_push($output,basename($filename,'.php'));
        }
        return $output;
    }

}