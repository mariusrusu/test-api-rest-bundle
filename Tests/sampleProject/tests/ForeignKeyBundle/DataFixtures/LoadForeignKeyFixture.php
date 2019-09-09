<?php  
	
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__."/../../../../../vendor/autoload.php";

use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Service\Api as ForeignKeyApi;

class LoadForeignKeyFixture
{
	public function execute($param)
    {
        $this->logCount = 1;

        $this->foreignKeyApi = new ForeignKeyApi($param['url'],$param['debug']);

        $parentData = [
            ["name" => "foo", "value"=>200],
            ["name" => "the answer", "value"=>42],
        ];
        $parents = $this->createParents($parentData);


        $childData = [
            ['parent'=> $parents[0]->id],
            ['parent'=> $parents[0]->id],
            ['parent'=> $parents[0]->id],
        ];

        $children = $this->createChildren($childData);

        copy(__DIR__."/../../../var/data/db_test/everycheckdb.db3", __DIR__."/../../../var/data/db_test/LoadForeignKeyFixture.sqlite");
        file_put_contents(__DIR__."/env.json", json_encode($this->env, JSON_PRETTY_PRINT));
    }

    private function store($object, $prefix)
    {
        $this->env[$prefix.$this->logCount] = $object;
        $this->logCount++;
    }

    private function createParents(array $data)
    {
        $parents = [];

        foreach($data as $item)
        {
            $parent = $this->foreignKeyApi->postData($item, 'parent');
            $this->store($parent->id, "parent_");
            $parents[] = $parent;
        }

        return $parents;
    }


    private function createChildren(array $data)
    {
        $childs = [];

        foreach($data as $item)
        {
            $child = $this->foreignKeyApi->postData($item, 'child');
            $this->store($child->id, "child_");
            $childs[] = $child;
        }

        return $childs;
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

	$test = new LoadForeignKeyFixture();
	$test->execute($arguments);
}
catch(\Exception $e)
{
	echo $e->getMessage();
	echo "\n";
}