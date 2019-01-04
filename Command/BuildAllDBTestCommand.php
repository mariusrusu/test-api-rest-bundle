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

class BuildAllDBTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('test:build:database')
            ->setDescription('Build the test databases and their fixtures files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $testfl = $this->getApplication()->find("test:fixture:list");
        $argumentsTestfl = [];

        $input = new ArrayInput($argumentsTestfl);
        $returnCode = $testfl->run($input, $output);

        if($returnCode != 0)
        {
            foreach($output as $item)
            {
                $testdataprep = $this->getApplication()->find("test:database:prepare");
                $argumentsTestdataprep = [$item.' --env=test  --ansi'];

                $input = new ArrayInput($argumentsTestdataprep);

                $output->writeln("build db for ".$item);

                $returnCode = $testdataprep->run($input, $output);

            }
        }
    }

}