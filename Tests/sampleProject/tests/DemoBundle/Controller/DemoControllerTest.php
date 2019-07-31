<?php
namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\tests\DemoBundle\Controller;

use EveryCheck\TestApiRestBundle\Controller\JsonApiAsArrayTestCase;
use EveryCheck\TestApiRestBundle\Loader\ResourcesFileLoader;

class DemoControllerTest extends JsonApiAsArrayTestCase
{
    const YAML_PROVIDER_FILENAME = "demo";
    const FIXTURE_FILENAME = "LoadDemoFixture";

    /**
     * @dataProvider ApiCallProvider
     */
    public function testAPICall($data_test)
    {
        $this->env = json_decode(file_get_contents(__DIR__."/../DataFixtures/env.json"), true);
        $this->genericTestAPICall($data_test);
    }

    public static function ApiCallProvider()
    {
        return ResourcesFileLoader::testCaseProvider(__DIR__,self::YAML_PROVIDER_FILENAME);
    }

    public function setUp($fixtureFilename = null)
    {
        parent::setUp(self::FIXTURE_FILENAME);
    }

}