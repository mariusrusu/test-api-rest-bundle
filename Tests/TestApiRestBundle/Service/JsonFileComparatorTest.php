<?php
namespace TestBundle\Service;

use TestBundle\Service\JsonFileComparator;

namespace TestBundle\Exceptions\ExtraKeyException;
namespace TestBundle\Exceptions\OptionalKeyRedefinedException;
namespace TestBundle\Exceptions\MissingKeyException;
namespace TestBundle\Exceptions\PatternNotMatchingException;
namespace TestBundle\Exceptions\ValueNotAnArrayException;

class JsonFileComparatorTest  extends \PHPUnit_Framework_TestCase
{


    protected function buildJsonFileComparator($left, $right, $matcherReturn = true, $sub_right = null)
    {

        $simpleFactory = $this->getMockBuilder('Coduo\PHPMatcher\Factory\SimpleFactory')
            ->setMethods(['createMatcher'])
            ->getMock();

        $matcher = $this->getMockBuilder('Coduo\PHPMatcher\MatcherInterface')
            ->setMethods(['match','getError'])
            ->getMock();

        $simpleFactory->method('createMatcher')->willReturn($matcher);
        $matcher->method('match')->willReturn($matcherReturn);
        $matcher->method('getError')->willReturn('mock error');

        // $matcher = $this->getMockBuilder('Coduo\PHPMatcher\MatcherInterface')
        //     ->setMethods(['match'])
        //     ->getMock();

        $jsonFileComparator = $this->getMockBuilder('TestBundle\Service\JsonFileComparator')
			->setMethods(['loadJSONFromString','loadJSONFromFile'])
			->setConstructorArgs([$simpleFactory])
            ->getMock();
        
        $jsonFileComparator->expects($this->exactly(1))->method('loadJSONFromString')->willReturn($left);

        $jsonFileComparator->expects($this->exactly(empty($sub_right) ? 1 : 2 ))
            ->method('loadJSONFromFile')
             ->will($this->onConsecutiveCalls($right, $sub_right));


        $jsonFileComparator->setLeftFromString('mocked');
        $jsonFileComparator->setRightFromFilename('mocked');
        $jsonFileComparator->setContextForDebug('');

        return $jsonFileComparator;
    }

    public function test_init()
    {
    	$left = [];
    	$right = [];

    	$jsonFileComparator = $this->buildJsonFileComparator($left,$right);

    	$this->assertNull($jsonFileComparator->compare());		
    }


    public function test_one_key()
    {
        $left = [
            "key1" => "value",
        ];
        $right = [
            "key1" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);

        $this->assertNull($jsonFileComparator->compare());      
    }

    public function test_two_key()
    {
        $left = [
            "key1" => "value",
            "key2" => "value",
        ];
        $right = [
            "key1" => "value",
            "key2" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);

        $this->assertNull($jsonFileComparator->compare());      
    }

    public function test_extra_key_on_left()
    {
        $left = [
            "key1" => "value",
            "key2" => "value",
        ];
        $right = [
            "key1" => "value"
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);

        $this->setExpectedException('TestBundle\Exceptions\ExtraKeyException');

        $this->assertNull($jsonFileComparator->compare());      
    }

    public function test_missing_key_on_left()
    {
        $left = [
            "key1" => "value",
        ];
        $right = [
            "key1" => "value",
            "key2" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);

        $this->setExpectedException('TestBundle\Exceptions\MissingKeyException');

        $this->assertNull($jsonFileComparator->compare());  
    }   


    public function test_optionnal_key()
    {
        $left = [
            "key1" => "value",
        ];
        $right = [
            "key1" => "value",
            "?key2" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);

        $this->assertNull($jsonFileComparator->compare());  
    }    
    
    public function test_optional_key_redefined()
    {
        $left = [
            "key1" => "value",
        ];
        $right = [
            "key1" => "value",
            "?key1" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);

        $this->setExpectedException('TestBundle\Exceptions\OptionalKeyRedefinedException');

        $this->assertNull($jsonFileComparator->compare());      
    }

    public function test_pattern_not_matching()
    {
        $left = [
            "key1" => "value",
        ];
        $right = [
            "key1" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right,false);

        $this->setExpectedException('TestBundle\Exceptions\PatternNotMatchingException');

        $this->assertNull($jsonFileComparator->compare());      
    }


    public function test_value_not_an_array_exception()
    {
        $left = [
            "key1" => [
                "subkey" => "not an array"
            ],
        ];
        $right = [
            "key1" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right,true,$right);

        $this->setExpectedException('TestBundle\Exceptions\ValueNotAnArrayException');

        $this->assertNull($jsonFileComparator->compare());      
    }


    public function test_load_sub_value_from_file()
    {
        $left = [
            "key1" => [
                ["subkey" => "value"],
                ["subkey" => "value"]
            ],
        ];
        $right = [
            "key1" => "value"
        ];
        $sub_right = [
            "subkey" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right,true,$sub_right);

        $this->assertNull($jsonFileComparator->compare());      
    }


    public function test_extra_key_in_sub_loaded_file()
    {
        $left = [
            "key1" => [
                ["subkey" => "value"],
                ["badKeyName" => "value"]
            ],
        ];
        $right = [
            "key1" => "value"
        ];
        $sub_right = [
            "subkey" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right,true,$sub_right);

        $this->setExpectedException('TestBundle\Exceptions\ExtraKeyException');

        $this->assertNull($jsonFileComparator->compare());      
    }

    public function test_capture_env_var()
    {
        $left = [
            "key1" => "value_l",
        ];
        $right = [
            "key1" => "#varname={{value_r}}",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);

        $this->assertNull($jsonFileComparator->compare());  

        $this->assertArrayHasKey( 'varname', $jsonFileComparator->getExtractedVar() );   
        $this->assertContains( 'value_l',  $jsonFileComparator->getExtractedVar()  );
    }

    public function test_capture_env_var_fail_on_array()
    {
        $left = [
            "key1" => "value",
        ];
        $right = [
            "key1" => [
                "key1" => "value",
            ],
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);

        $this->assertNull($jsonFileComparator->compare());  

    }

    public function test_capture_env_fail_on_casse()
    {
        $left = [
            "key1" => "value",
        ];
        $right = [
            "key1" => "#authTokens={{@string@}}",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);

        $this->assertNull($jsonFileComparator->compare());  

        $this->assertArrayHasKey( 'authTokens', $jsonFileComparator->getExtractedVar() );   
        $this->assertContains( 'value',  $jsonFileComparator->getExtractedVar()  );
    }

    public function test_dont_match_dont_throw_exception_extra_key()
    {
        $left = [
            "key1" => "value",
        ];
        $right = [
            "!key1" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right,false);

        $this->assertNull($jsonFileComparator->compare());      
    }

    public function test_1_dont_match_throw_exception_redefined_key()
    {
        $left = [
            "key1" => "value",
        ];
        $right = [
            "!key1" => "value",
            "key1" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);

        $this->setExpectedException('TestBundle\Exceptions\OptionalKeyRedefinedException');

        $jsonFileComparator->compare();      
    }    

    public function test_2_dont_match_throw_exception_redefined_key()
    {
        $left = [
            "key1" => "value",
        ];
        $right = [
            "!key1" => "value",
            "?key1" => "value",
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);

        $this->setExpectedException('TestBundle\Exceptions\OptionalKeyRedefinedException');

        $jsonFileComparator->compare();      
    }

    public function test_dont_match_throw_exception_on_array()
    {
        $left = [
            "key1" => [
                "subkey" => "not an array"
            ],
        ];
        $right = [
            "!key1" => [
                "subkey" => "not an array"
            ],
        ];

        $jsonFileComparator = $this->buildJsonFileComparator($left,$right);
        
        $this->expectException('TestBundle\Exceptions\DontMatchDoesNotWorkWithArray');
        
        $jsonFileComparator->compare();  

    }
}
