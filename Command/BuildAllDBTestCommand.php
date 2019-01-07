<?php
namespace EveryCheck\TestApiRestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class BuildAllDBTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('test:database:build-all')
            ->setDescription('Build the test databases and their fixtures files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $listAllFixtureCommand = $this->getApplication()->find("test:fixture:list");

        $listAllFixtureLauncher = new CommandTester($listAllFixtureCommand);
        $listAllFixtureLauncher->execute(
            [
                "command" => $listAllFixtureCommand->getName(),
            ]
        );

        $listAllFixtureResult = $listAllFixtureLauncher->getDisplay();

        if(!empty($listAllFixtureResult))
        {
            $in = explode(" ", rtrim($listAllFixtureResult));
            foreach ($in as $item)
            {
                $output->writeln("Build db for ".$item);
                $databasePrepareCommand = $this->getApplication()->find("test:database:build-one");
                $databasePrepareArguments = array(
                    'command' => $databasePrepareCommand->getName(),
                    'fixture' => $item
                );

                $databasePrepareInput= new ArrayInput($databasePrepareArguments);
                $databasePrepareReturn = $databasePrepareCommand->run($databasePrepareInput, $output);
            }

            $output->writeln("Database is ready to be tested !");
        }
        else
        {
            $output->writeln("No fixtures file have been found.");
        }

    }

}