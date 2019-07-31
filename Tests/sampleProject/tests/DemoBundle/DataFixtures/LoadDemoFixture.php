<?php  
	
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__."/../../../../../vendor/autoload.php";

use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\DemoBundle\Service\Api as DemoApi;
use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\DemoBundle\Entity\Demo;

class LoadDemoFixture
{
	public function execute($param)
    {
        $this->logCount = 1;

        $this->demoApi = new DemoApi($param['url'],$param['debug']);

        $demo = $this->createDemo();

        copy(__DIR__."/../../../var/data/db_test/everycheckdb.db3", __DIR__."/../../../var/data/db_test/LoadDemoFixture.sqlite");
        file_put_contents(__DIR__."/env.json", json_encode($this->env, JSON_PRETTY_PRINT));
    }

    private function store($object, $prefix)
    {
        $this->env[$prefix.$this->logCount] = $object;
        $this->logCount++;
    }

    private function createDemo()
    {
        $demos = [];
        $demo1 = [
            "name" => "foo",
            "value" => 300
        ];

        $demo2 = [
            "name" => "the answer",
            "value" => 42
        ];

        $demo1 = $this->demoApi->postDemo($demo1);
        $this->store($demo1->id, "demo_");
        $demos[] = $demo1;

        $demo2 = $this->demoApi->postDemo($demo2);
        $this->store($demo2->id, "demo_");
        $demos[] = $demo2;
    }
}

try
{
	if(count($argv) < 1)
	{
		throw new \Exception("Usage : \n command url [debug]");
	}
	$arguments = [
		"url"    => $argv[1],
		"debug"  => $argv[2],
	];

	$test = new LoadDemoFixture();
	$test->execute($arguments);
}
catch(\Exception $e)
{
	echo $e->getMessage();
	echo "\n";
}