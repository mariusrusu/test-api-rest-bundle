<?php
namespace EveryCheck\TestApiRestBundle\Tests\TestApiRestBundle\Command;

use EveryCheck\TestApiRestBundle\Command\ListAllTestFixtureAvailableCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ListAllTestFixtureAvailableCommandTest extends KernelTestCase
{
    protected $client;
    protected $application;

    public function testListAll()
    {
        $kernel = static::createKernel();

        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);

        $listFixtureCommand = new ListAllTestFixtureAvailableCommand();
        $this->application->add($listFixtureCommand);

        $command = $this->application->find("test:fixture:list");

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                "command" => $command->getName(),
            ]
        );
        $output = $commandTester->getDisplay();

        $this->assertEquals("LoadDemoFixture LoadEmailFixture LoadPatternFixture\n", $output);
    }
}