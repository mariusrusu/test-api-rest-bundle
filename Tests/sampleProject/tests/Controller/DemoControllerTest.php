<?php
namespace EveryCheck\TestApiRestBundle\Doc\Example\sampleProject\tests\Controller;

use EveryCheck\TestApiRestBundle\Controller\JsonApiAsArrayTestCase;
use EveryCheck\TestApiRestBundle\Loader\ResourcesFileLoader;

class DemoControllerTest extends JsonApiAsArrayTestCase
{
    /**
     * @dataProvider ApiCallProvider
     */
    public function testAPICall($data_test)
    {
        $this->genericTestAPICall($data_test);
    }

    public static function ApiCallProvider()
    {
        return ResourcesFileLoader::testCaseProvider(__DIR__,"demo");
    }

    public function setUp($fixtureFilename = null)
    {
        parent::setUp("LoadDemoFixtures");
    }

}