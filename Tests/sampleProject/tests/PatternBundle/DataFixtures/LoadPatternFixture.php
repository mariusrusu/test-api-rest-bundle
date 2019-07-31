<?php  
	
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__."/../../../../../vendor/autoload.php";

use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\PatternBundle\Service\Api as PatternApi;
use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\PatternBundle\Entity\Pattern;

class LoadPatternFixture
{
	public function execute($param)
    {
        $this->logCount = 1;

        $this->patternApi = new PatternApi($param['url'],$param['debug']);

        $pattern = $this->createPattern();

        copy(__DIR__."/../../../var/data/db_test/everycheckdb.db3", __DIR__."/../../../var/data/db_test/LoadPatternFixture.sqlite");
        file_put_contents(__DIR__."/env.json", json_encode($this->env, JSON_PRETTY_PRINT));
    }

    private function store($object, $prefix)
    {
        $this->env[$prefix.$this->logCount] = $object;
        $this->logCount++;
    }

    private function createPattern()
    {
        $patterns = [];
        $pattern1 = [
            "name" => "foo",
            "value" => 300
        ];

        $pattern2 = [
            "name" => "the answer",
            "value" => 42
        ];

        $pattern1 = $this->patternApi->postPattern($pattern1);
        $this->store($pattern1->uuid, "pattern_");
        $patterns[] = $pattern1;

        $pattern2 = $this->patternApi->postPattern($pattern2);
        $this->store($pattern2->uuid, "pattern_");
        $patterns[] = $pattern2;
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

	$test = new LoadPatternFixture();
	$test->execute($arguments);
}
catch(\Exception $e)
{
	echo $e->getMessage();
	echo "\n";
}