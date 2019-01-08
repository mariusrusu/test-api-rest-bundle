<?php
namespace EveryCheck\TestApiRestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class BuildAllDBTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('test:database:build-all')
            ->setDescription('Build the test databases and their fixtures files')
            ->addOption(
                'bin',
                null,
                InputOption::VALUE_OPTIONAL,
                'Set the path for the console.',
                "bin/console"
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $application->setAutoExit(false);
        $listAllFixtureCommand = $application->find("test:fixture:list");

        $listAllFixtureLauncher = new CommandTester($listAllFixtureCommand);
        $listAllFixtureLauncher->execute(
            [
                "command" => $listAllFixtureCommand->getName(),
                '--env'   => "test"
            ]
        );

        $listAllFixtureResult = $listAllFixtureLauncher->getDisplay();

        if(!empty($listAllFixtureResult))
        {
            $in = explode(" ", rtrim($listAllFixtureResult));
            foreach ($in as $item)
            {
                $output->writeln("Build db for ".$item);
                shell_exec($input->getOption('bin')." test:data:build-one $item --env=test  --ansi");
            }

            $output->writeln("Database is ready to be tested !");
        }
        else
        {
            $output->writeln("No fixtures file have been found.");
        }

    }

}