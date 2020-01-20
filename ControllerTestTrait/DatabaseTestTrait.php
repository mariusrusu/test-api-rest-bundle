<?php
namespace EveryCheck\TestApiRestBundle\ControllerTestTrait;

use EveryCheck\TestApiRestBundle\Entity\TestDataChunk;
use EveryCheck\TestApiRestBundle\Matcher\Matcher;
use EveryCheck\TestApiRestBundle\Service\JsonFileComparator;

trait DatabaseTestTrait
{
    public function assertDatabase(TestDataChunk $dataTest)
    {
        if($dataTest->kind != TestDataChunk::KIND_DATABASE)
        {
            return;
        }

        $this->extractValueFromEnvIfNeeded($dataTest->data);

        $entities = [];
        try {
            $re = '/^([a-zA-Z]+:[a-zA-Z]+)\?([a-zA-Z_-]+)=(.+)$/m';
            $str =  $dataTest->data['url'];

            preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

            $repository = $matches[0][1];
            $name = $matches[0][2];
            $value = $matches[0][3];

            $entities = $this->em->getRepository($repository)->findBy([$name=>$value]);
			$this->em->clear();
        }
        catch(\Exception $e)
        {
            $this->fail("Cannot parse db url : ". $e->getMessage());
        }

        $jsonContent = static::$kernel->getContainer()
            ->get('jms_serializer')
            ->serialize($entities, "json");

        $calledClass = get_called_class();
        $calledClassFolder = dirname((new \ReflectionClass($calledClass))->getFileName());

        $jsonFileComparator = new JsonFileComparator(new Matcher());
        $jsonFileComparator->setFilePath($calledClassFolder,'..',$this->responsesDir);
        $jsonFileComparator->setLeftFromString($jsonContent);
        $jsonFileComparator->setRightFromFilename($dataTest->data['out']);
        $jsonFileComparator->setContextForDebug($dataTest->data['out']);

        try
        {
            $this->assertNull($jsonFileComparator->compare());
        }
        catch (\Exception $e)
        {
            $this->fail($e->getMessage(). "\n\n". $jsonContent);
        }

    }
}
?>