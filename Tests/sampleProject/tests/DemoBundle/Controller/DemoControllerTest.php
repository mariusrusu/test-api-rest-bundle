<?php
namespace EveryCheck\TestApiRestBundle\Doc\Example\sampleProject\tests\DemoBundle\Controller;

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