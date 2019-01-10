<?php
namespace EveryCheck\TestApiRestBundle\Tests\TestApiRestBundle\Command;


use EveryCheck\TestApiRestBundle\Command\BuildAllDBTestCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class BuildAllTestCommandTest extends KernelTestCase
{
    protected $client;
    protected $application;

    public function testBuildTestFixture()
    {
        $kernel = static::createKernel();

        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);

        $buildDatabaseCommand = new BuildAllDBTestCommand();
        $this->application->add($buildDatabaseCommand);

        $command = $this->application->find("test:database:build-all");

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                "command" => $command->getName(),
                "--bin" => "./Tests/sampleProject/app/console"
            ]
        );
        $output = $commandTester->getDisplay();

        $this->assertContains("Build db for LoadDemoFixture", $output);
        $this->assertContains("Build db for LoadPatternFixture", $output);
        $this->assertContains("Database is ready to be tested !", $output);
    }
}