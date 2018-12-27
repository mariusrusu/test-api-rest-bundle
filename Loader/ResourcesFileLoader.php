<?php
namespace EveryCheck\TestApiRestBundle\Loader;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use EveryCheck\TestApiRestBundle\Entity\TestDataChunk;

/**
 * YamlFileLoader loads Yaml routing files.
 */
class ResourcesFileLoader
{

    const PATH_TO_RESOURCES_FILE = '/../Resources/config';
    const JSON_HEADER = 'application/json';
    const PDF_HEADER  = 'application/pdf';
    const PNG_HEADER  = 'image/png';

    public static function testCaseProvider($folder,$filename)
    {
        $ymlLoader = new YamlFileLoader(new FileLocator($folder.self::PATH_TO_RESOURCES_FILE));

        $config = $ymlLoader->load($filename.'.yaml');

        $data = array();
        self::formatUnitTestSectionData($config,$data,$filename);
        self::formatScenarioSectionData($config,$data,$filename);
        return $data;
    }  

    public static function formatUnitTestSectionData($data_in,&$data_out,$filename)
    {
        if(array_key_exists('unit_tests', $data_in))
        {
            foreach ($data_in['unit_tests'] as $action => $tests) 
            {
                foreach ($tests as  $test) 
                {                
                    $testDataChunk = new TestDataChunk(TestDataChunk::KIND_UNIT_TEST,$filename);
                    $testDataChunk->setData(self::formatData($test));
                    $testDataChunk->data['action'] = $action;
                    $data_out[] = [$testDataChunk];
                }
            }
        }  
    }

    public static function formatScenarioSectionData($data_in,&$data_out,$filename)
    {
        if(array_key_exists('scenario', $data_in))
        {
            foreach ($data_in['scenario'] as $scenario_name => $tests) 
            {
                $testDataChunk = new TestDataChunk(TestDataChunk::KIND_SCENARIO,$filename);
                foreach ($tests as  $test) 
                {
                    $data = self::formatData($test);
                    $kind = $data['action'] == "DB" ? TestDataChunk::KIND_DATABASE :TestDataChunk::KIND_UNIT_TEST;
                    $subTestDataChunk = new TestDataChunk($kind,$filename);
                    $subTestDataChunk->setData(self::formatData($test));
                    $testDataChunk->data[] = $subTestDataChunk;
                }
                $data_out[]=[$testDataChunk];
            }
        }
    }

    public static function formatData($array)
    {
        return [
            'action'            => self::getValueOrDefault( $array,['action' ], 'GET'), 
            'url'               => self::getValueOrDefault( $array,['url'    ]) ,
            'out'               => self::getValueOrDefault( $array,['out'    ]) ,
            'status'            => self::getValueOrDefault( $array,['status' ], 200) , 
            'in'                => self::getValueOrDefault( $array,['in'     ]) , 
            'headers'           => self::getValueOrDefault( $array,['headers']) , 
            'content_type_in'   => self::getValueOrDefault( $array,['ct_in']  , self::JSON_HEADER) , 
            'content_type_out'  => self::getValueOrDefault( $array,['ct_out'] , self::JSON_HEADER) , 
            'mail'              => self::getValueOrDefault( $array,['mail']) , 
            'pcre_mail'         => self::getValueOrDefault( $array,['pcre_mail']) , 
        ];
    }


    public static function getValueOrDefault($array , array $keys, $defaultValue = null) 
    {
        foreach ( $keys as $key ) 
        {
            if ( is_array($array) and array_key_exists($key, $array))
            {
                $array = $array[$key];
            }
            else
            {
                return $defaultValue;
            }
        }

        return $array;
    }




}