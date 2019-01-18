<?php
namespace EveryCheck\TestApiRestBundle\Controller;

use EveryCheck\TestApiRestBundle\Entity\TestDataChunk;
use EveryCheck\TestApiRestBundle\ControllerTestTrait\DatabaseTestTrait;
use EveryCheck\TestApiRestBundle\ControllerTestTrait\ApiCallTestTrait;
use EveryCheck\TestApiRestBundle\ControllerTestTrait\EmailTestTrait;

class JsonApiAsArrayTestCase extends AbstractBaseControllerTestClass
{
    use DatabaseTestTrait;
    use ApiCallTestTrait;
    use EmailTestTrait;

    const JSON_HEADER = 'application/json';
    const PDF_HEADER  = 'application/pdf';
    const PNG_HEADER  = 'image/png';

    protected  $env = [];
    protected  $current;

    public function onNotSuccessfulTest(\Throwable $t){
        if($this->current instanceof TestDataChunk)
        {
            dump($this->current->data);
        }
        parent::onNotSuccessfulTest($t);
    }


    public function genericTestAPICall(TestDataChunk $dataTest)
    {
        $this->payloadsDir = $this->client->getKernel()->getContainer()->getParameter('test_api_rest.directory.payloads');
        $this->responsesDir = $this->client->getKernel()->getContainer()->getParameter('test_api_rest.directory.responses');
        $this->current = $dataTest;
        if($dataTest->kind == TestDataChunk::KIND_SCENARIO)
        {
            foreach ($dataTest->data as $subDataTest) 
            {
                $this->genericTestAPICall($subDataTest);
            }
        }
        else if($dataTest->kind == TestDataChunk::KIND_DATABASE)
        {
            $this->assertDatabase($dataTest);
        }
        else if($dataTest->kind == TestDataChunk::KIND_UNIT_TEST)
        {
           $this->assertApiRestCall($dataTest);
          
        }
    }

    protected function extractValueFromEnvIfNeeded(&$array)
    {
        foreach ($array as $key => $value) 
        {
            $array[$key] = $this->getReferencedEnvVariableOrValue($value);
        }
    }

    protected function getReferencedEnvVariableOrValue($value)
    {
        $match = [];
        while (preg_match('/#(\w+)#/', $value,$match))
        {
            $value = str_replace('#'.$match[1].'#', $this->env[$match[1]], $value);
        }
        return $value;
    }


}