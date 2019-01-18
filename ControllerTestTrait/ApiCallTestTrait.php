<?php
namespace EveryCheck\TestApiRestBundle\ControllerTestTrait;

use Symfony\Component\HttpFoundation\Response;
use EveryCheck\TestApiRestBundle\Entity\TestDataChunk;
use EveryCheck\TestApiRestBundle\Matcher\Matcher;
use EveryCheck\TestApiRestBundle\Service\JsonFileComparator;
use EveryCheck\TestApiRestBundle\ControllerTestTrait\EmailTestTrait;

trait ApiCallTestTrait
{
    public function assertApiRestCall(TestDataChunk $dataTest)
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


    protected function getPayloadAsString($filename,$mime_type)
    {
        if ($filename == null) return null;

        $ext =  $this->getExtensionFromMimeType($mime_type);

        $calledClass = get_called_class();
        $calledClassFolder = dirname((new \ReflectionClass($calledClass))->getFileName());

        $fullpath = join(DIRECTORY_SEPARATOR,[$calledClassFolder,'..', $this->payloadsDir,$filename . '.' . $ext]);
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


            if($expected_content_type == $this::JSON_HEADER)
            {
                $jsonFileComparator = new JsonFileComparator(new Matcher());
                try
                {

                    $jsonFileComparator->setFilePath($calledClassFolder,'..', $this->responsesDir);
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
                $filepath = join(DIRECTORY_SEPARATOR,[$calledClassFolder,'..', $this->responsesDir,$filename]);
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

    public function getExtensionFromMimeType ($mime_type)
    {
        $extensions = [
            $this::PDF_HEADER => 'pdf',
            $this::JSON_HEADER => 'json',
            $this::PNG_HEADER => 'png',
            'image/x-png'   => 'png',
            'image/jpg'     => 'jpg',
            'image/jpeg'    => 'jpg',
            'image/pjpeg'   => 'jpg'
        ];

        return $extensions[$mime_type];
    }
}