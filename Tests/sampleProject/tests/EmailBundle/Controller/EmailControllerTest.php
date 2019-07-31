<?php
namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\tests\PatternBundle\Controller;

use EveryCheck\TestApiRestBundle\Controller\JsonApiAsArrayTestCase;
use EveryCheck\TestApiRestBundle\Loader\ResourcesFileLoader;

class EmailControllerTest extends JsonApiAsArrayTestCase
{
    const YAML_PROVIDER_FILENAME = "email";
    const FIXTURE_FILENAME = "LoadEmailFixture";

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