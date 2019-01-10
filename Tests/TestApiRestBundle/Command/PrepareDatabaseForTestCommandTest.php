<?php
namespace EveryCheck\TestApiRestBundle\Tests\TestApiRestBundle\Command;


use EveryCheck\TestApiRestBundle\Command\PrepareDatabaseForTestCommand;
use EveryCheck\TestApiRestBundle\Doc\Example\sampleProject\tests\DemoBundle\Controller\DemoControllerTest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PrepareDatabaseForTestCommandTest extends KernelTestCase
{
    protected $client;
    protected $application;

    public function testPrepareDatabaseWithFixture()
    {
        $kernel = static::createKernel();

        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);

        $prepareDatabaseCommand = new PrepareDatabaseForTestCommand();
        $this->application->add($prepareDatabaseCommand);

        $command = $this->application->find("test:database:build-one");

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                "command" => $command->getName(),
                "fixture" => DemoControllerTest::FIXTURE_FILENAME,
                "--path" =>"../var/data/db_test/",
                "--env" => "test",
            ]
        );
        $output = $commandTester->getDisplay();

        $this->assertContains("loading Doctrine\Bundle\FixturesBundle\EmptyFixture", $output);
        $this->assertContains("loading EveryCheck\TestApiRestBundle\Tests\sampleProject\\tests\DemoBundle\DataFixtures\ORM\\".DemoControllerTest::FIXTURE_FILENAME, $output);

    }
}