<?php
namespace EveryCheck\TestApiRestBundle\Tests\TestApiRestBundle\Command;


use EveryCheck\TestApiRestBundle\Command\BuildAllDBTestCommand;
use EveryCheck\TestApiRestBundle\Doc\Example\sampleProject\tests\Controller\DemoControllerTest;
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
            ]
        );
        $output = $commandTester->getDisplay();

        $this->assertContains("Build db for ".DemoControllerTest::FIXTURE_FILENAME, $output);
        $this->assertContains("loading Doctrine\Bundle\FixturesBundle\EmptyFixture", $output);
        $this->assertContains("loading EveryCheck\TestApiRestBundle\Tests\sampleProject\\tests\DataFixtures\ORM\\".DemoControllerTest::FIXTURE_FILENAME, $output);
        $this->assertContains("Database is ready to be tested !", $output);

    }
}