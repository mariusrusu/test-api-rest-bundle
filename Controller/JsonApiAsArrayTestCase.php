<?php
namespace EveryCheck\TestApiRestBundle\Controller;

use Coduo\PHPMatcher\Factory\SimpleFactory;
use Symfony\Component\HttpFoundation\Response;
 
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use EveryCheck\TestApiRestBundle\Loader\YamlFileLoader;
use EveryCheck\TestApiRestBundle\Service\JsonFileComparator;

use EveryCheck\TestApiRestBundle\Entity\TestDataChunk;


class JsonApiAsArrayTestCase extends AbstractBaseControllerTestClass
{

    const JSON_HEADER = 'application/json';
    const PDF_HEADER  = 'application/pdf';
    const PNG_HEADER  = 'image/png';

    protected  $env = [];
    protected  $current;

    public function onNotSuccessfulTest(Throwable $t){
        if($this->current instanceof TestDataChunk)
        {
            dump($this->current->data);
        }
        parent::onNotSuccessfulTest($t);
    }


    public function genericTestAPICall(TestDataChunk $dataTest)
    {
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
                foreach ($entities as $entity)
                {
                    $this->em->detach($entity);
                }
            }
            catch(\Exception $e)
            {
                $this->fail("Cannot parse db url : ". $e->getMessage());
            }
            $jsonContent = static::$kernel->getContainer()->get('jms_serializer')->serialize($entities,'json');

            $calledClass = get_called_class();
            $calledClassFolder = dirname((new \ReflectionClass($calledClass))->getFileName());

            $jsonFileComparator = new JsonFileComparator(new SimpleFactory);
            $jsonFileComparator->setFilePath($calledClassFolder,'..', 'Responses','Expected');
            $jsonFileComparator->setLeftFromString($jsonContent);
            $jsonFileComparator->setRightFromFilename($dataTest->data['out']);
            $jsonFileComparator->setContextForDebug($dataTest->data['out']);

            try
            {
                $this->assertTrue($jsonFileComparator->compare());
            }
            catch (\Exception $e) 
            {
                $this->fail($e->getMessage(). "\n\n". $jsonContent);
            }
        }
        else if($dataTest->kind == TestDataChunk::KIND_UNIT_TEST)
        {
            $data_test = $dataTest->data;
            $this->extractValueFromEnvIfNeeded($data_test);

            if(empty($data_test['mail']) == false || empty($data_test['pcre_mail']) == false )
            {
               $this->enableMailCatching();
            }

            $arrayHeaders = $this->explodeHeadersAsArray($data_test['headers']);
            $arrayHeaders['CONTENT_TYPE'] = $data_test['content_type_in'];

            $this->client->request(
                $data_test['action'],
                $data_test['url'],
                [],
                [],
                $arrayHeaders,
                $this->getPayloadAsString($data_test['in'],$data_test['content_type_in'])
            );

            $this->assertResponseCode($this->client->getResponse(), $data_test['status']);
            $this->assertResponse($this->client->getResponse(), $data_test['out'],$data_test['content_type_out']);  

            if(empty($data_test['mail']) == false )
            {
               $this->assertMailSendedCount($data_test['mail']);
            }

            if(empty($data_test['pcre_mail']) == false )
            {
               $this->collectEmailAndTestContent(0,$data_test['pcre_mail']);
            }
          
        }
    }

    protected function enableMailCatching()
    {
        $this->client->enableProfiler();    
    }

    protected function collectEmailAndTestContent($mail,$pcre)
    {
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        $this->assertLessThan($mailCollector->getMessageCount(),$mail,"cannot read mail ". $mail ." only " . $mailCollector->getMessageCount() . "mail sended");
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];
        preg_match($pcre, $message->getBody(),$exctractedValue);
        foreach ($exctractedValue as $key => $value) {
            $this->env['pcre'.$key] = $value;
        }
    }

    protected function assertMailSendedCount($count)
    {
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals($count, $mailCollector->getMessageCount(),"failed to expecting $count mails got ". $mailCollector->getMessageCount());
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

    protected function getPayloadAsString($filename,$mime_type)
    {
        if ($filename == null) return null;

        $ext =  JsonApiAsArrayTestCase::getExtensionFromMimeType($mime_type);

        $calledClass = get_called_class();
        $calledClassFolder = dirname((new \ReflectionClass($calledClass))->getFileName());
        
        $fullpath = join(DIRECTORY_SEPARATOR,[$calledClassFolder,'..', 'Payloads',$filename . '.' . $ext]);
        $file_content =  file_get_contents($fullpath);

        return $this->getReferencedEnvVariableOrValue($file_content);
    }

    protected function getErrorMsg($pageContent,$page_header = '')
    {
        if ($page_header != '') $page_header = $page_header . "\n\n";

        $matchs = [];
        preg_match('/<title>(.+)<\/title>/s', $pageContent,$matchs);

        if(array_key_exists(1, $matchs))
        {
            return $matchs[1];
        }
        return $page_header . $pageContent;
    }

    protected function assertResponseCode(Response $response, $expectedStatusCode)
    {
        $currentStatusCode = $response->getStatusCode();
        self::assertEquals($expectedStatusCode, $response->getStatusCode(), $this->getErrorMsg($response->getContent()));
    }

    protected function assertResponse(Response $response, $filename, $expected_content_type)
    {

        if($filename != null)
        {
            self::assertTrue( 
                $response->headers->contains('Content-Type', $expected_content_type),
                $this->getErrorMsg($response->getContent(),$response->headers->__tostring())
            );

            $calledClass = get_called_class();
            $calledClassFolder = dirname((new \ReflectionClass($calledClass))->getFileName());


            if($expected_content_type == JsonApiAsArrayTestCase::JSON_HEADER)
            {
                $jsonFileComparator = new JsonFileComparator(new SimpleFactory);
                try
                {

                    $jsonFileComparator->setFilePath($calledClassFolder,'..', 'Responses','Expected');
                    $jsonFileComparator->setLeftFromString($response->getContent());
                    $jsonFileComparator->setRightFromFilename($filename);
                    $jsonFileComparator->setContextForDebug($filename);
                    $jsonFileComparator->compare();

                    $this->env = array_merge($this->env, $jsonFileComparator->getExtractedVar());
                }
                catch (\Exception $e) 
                {
                    $this->fail($e->getMessage(). "\n\n". $response->getContent());
                }
            }
            else
            {
                $filepath = join(DIRECTORY_SEPARATOR,[$calledClassFolder,'..', 'Responses','Expected',$filename]);
                $this->assertFileExists($filepath,"Cannot find expected file at : " . $filepath);
                $fileContent = file_get_contents($filepath);

                $this->assertEquals($fileContent,$response->getContent(),"File content doesn't match requirement ".$fileContent);
            }
        }
    }

    protected function _assertArrayResponseContent($response, $filename)
    {
        $this->lastResponse = $response;
        $decodedResponse          = json_decode($response,true);
        $decodedExpectedResponse  = json_decode($this->_loadExpectedResponse($filename.'.json'),true);
        $this->assertInternalType('array',$decodedResponse,'Error while decoding jspon response : '. $this->getJsonLastError());
        $this->assertInternalType('array',
            $decodedExpectedResponse,'Error while decoding jspon expected response : '. $this->getJsonLastError());
        $this->matchArray($decodedResponse,$decodedExpectedResponse,$filename);
    }

    private function explodeHeadersAsArray($headers)
    {
        $out = [];
        if($headers != null)
        {
            $headersSplitted = explode(";", $headers);

            foreach ($headersSplitted as $header) {
                list($key,$value) = explode(":", $header);
                $out['HTTP_'.trim($key)] = trim($value);
            }
        }
        return $out;
    }

    public static function getExtensionFromMimeType ($mime_type)
    {
        $extensions = [
            JsonApiAsArrayTestCase::PDF_HEADER => 'pdf',
            JsonApiAsArrayTestCase::JSON_HEADER => 'json',
            JsonApiAsArrayTestCase::PNG_HEADER => 'png',
            'image/x-png'   => 'png',
            'image/jpg'     => 'jpg',
            'image/jpeg'    => 'jpg',
            'image/pjpeg'   => 'jpg'
        ];

        return $extensions[$mime_type];
    }
}