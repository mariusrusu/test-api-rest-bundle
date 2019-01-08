<?php
namespace EveryCheck\TestApiRestBundle\Service;

use EveryCheck\TestApiRestBundle\Exceptions\ExtraKeyException;
use EveryCheck\TestApiRestBundle\Exceptions\OptionalKeyRedefinedException;
use EveryCheck\TestApiRestBundle\Exceptions\MissingKeyException;
use EveryCheck\TestApiRestBundle\Exceptions\PatternNotMatchingException;
use EveryCheck\TestApiRestBundle\Exceptions\PatternMatchingException;
use EveryCheck\TestApiRestBundle\Exceptions\ValueNotAnArrayException;
use EveryCheck\TestApiRestBundle\Exceptions\FileNotFoundException;
use EveryCheck\TestApiRestBundle\Exceptions\DontMatchDoesNotWorkWithArray;
use EveryCheck\TestApiRestBundle\Matcher\Matcher;

class JsonFileComparator 
{
    protected $matcher = null;

    protected $left = null;
    protected $right = null;
    protected $context = '';
    protected $pathPart = array();
    protected $extractedVar = [];

    function __construct(Matcher $matcher)
    {
        $this->matcher = $matcher;
    }

    public function setLeftFromString($string)
    {
        $this->left = $this->loadJSONFromString($string);
    }

    public  function setRightFromFilename($filename)
    {
        $this->right = $this->loadJSONFromFile($filename);
    }

    public  function setContextForDebug($string)
    {
        $this->context = $string;
    }

    public function setFilePath(...$pathPart)
    {
        $this->pathPart = $pathPart;
    }

    public function getExtractedVar()
    {
        return $this->extractedVar;
    }


    public function setExtractedVarValue($key,$value)
    {
       $this->extractedVar[$key] = $value;
    }

    public function compare()
    {
        $this->matchJsonAsArray($this->left,$this->right,$this->context);
    }

    protected function loadJSONFromString($string, $src='string')
    {
        $data_decoded = json_decode($string,true);
        if( !is_array($data_decoded) ) throw new \Exception('Error while decoding json from '.$src.' : '. $this->getJsonLastError());

        return $data_decoded;
    }

    protected function loadJSONFromFile($filename)
    {
        $fullPath = join(DIRECTORY_SEPARATOR,array_merge($this->pathPart,[$filename.'.json']));
        $data = file_get_contents($fullPath);
        if( FALSE === $data ) throw new FileNotFoundException('Error while loading ' . $filename);
        return $this->loadJSONFromString($data,'file : '. $filename);
    }

    protected function capturingEnvIfExist($leftArray,&$rightArray,$key)
    {
        if($this->isValueDefiningAnEnvVar($rightArray[$key]))
        {
            $def = $this->splitValueAndEnvVarName($rightArray[$key]);
            $this->setExtractedVarValue($def['name'],$leftArray[$key]);
            $rightArray[$key] = $def['value'];
        }
    }

    protected function isValueDefiningAnEnvVar($value)
    {
        return is_string($value) && preg_match('/^#\w+={{.+}}$/', $value);
    }

    protected function splitValueAndEnvVarName($value)
    {
        $match = [];
        preg_match('/^#(\w+)={{(.+)}}$/', $value,$match);

        return [
            'name' => $match[1],
            'value' => $match[2],
        ];
    }

    protected function matchJsonAsArray($a,$b,$contextKey)
    {
        foreach ($a as $key => $value)
        {
            $hasKey = array_key_exists($key, $b);
            $hasOptionnalKey = array_key_exists('?'.$key, $b);
            $hasDontMatchKey = array_key_exists('!'.$key, $b);

            if(  !$hasKey &&  !$hasOptionnalKey && !$hasDontMatchKey ) 
            {
                throw new ExtraKeyException('Extra key :' .$contextKey.'->'.$key);
            }
            if(   $hasKey && $hasOptionnalKey || $hasKey && $hasDontMatchKey  || $hasDontMatchKey && $hasOptionnalKey  ) 
            {
                throw new OptionalKeyRedefinedException('Key define multiple times :' .$contextKey.'->'.$key);
            }

            if( $hasOptionnalKey )
            {   
                $b[$key] = $b['?'.$key];
                unset($b['?'.$key]);
            }

            if( $hasDontMatchKey )
            {   
                $b[$key] = $b['!'.$key];
                unset($b['!'.$key]);
            }

            
            $this->capturingEnvIfExist($a,$b,$key);

            if( is_array($value))
            {
                if( $hasDontMatchKey )
                {   
                    throw new DontMatchDoesNotWorkWithArray('Cannot define Dont match key on array value :' .$contextKey.'->'.$key);
                }

                if (is_array($b[$key]))
                {
                    $this->matchJsonAsArray($value,$b[$key],$contextKey.'->'.$key);
                }
                else 
                {
                    $right =  $this->loadJSONFromFile($b[$key]);
                    foreach ($value as $subkey => $subvalue)
                    {                   
                        $currentKey  =  $contextKey.'->'.$key.'['.$b[$key].']->'.$subkey;
                        if( ! is_array($subvalue) ) throw new ValueNotAnArrayException($currentKey . ' must be an array!');
                        
                        $this->matchJsonAsArray($subvalue,$right,$currentKey);
                    }
                }
            }
            else
            {
                $mustFailed = $hasDontMatchKey ? true : false;
                $this->matchValue($value,$b[$key],$contextKey.'->'.$key,$mustFailed);
            }
        }
        foreach ($b as $key => $value)
        {
            if( substr($key,0,1) != '?' )
            {
                if (!array_key_exists($key, $a)) throw new MissingKeyException('Missing key :' .$contextKey.'->'.$key);                
            }
        }
    }

    protected function matchValue( $left, $right,$key, $mustFailed = false)
    {

        $result = $this->matcher->match($left, $right);
        if ($result == false && $mustFailed == false) 
        {
            throw new PatternNotMatchingException('does not match in : ' . $key . ' : ' . $this->matcher->getError());
        }
        elseif($result == true && $mustFailed == true)
        {
            throw new PatternMatchingException('should not match in : ' . $key . ' : ' . $this->matcher->getError());
        }
    }

    protected function getJsonLastError()
    {
       switch (json_last_error()) 
       {
            case JSON_ERROR_NONE:
                return ' - Aucune erreur';
                break;
            case JSON_ERROR_DEPTH:
                return ' - Profondeur maximale atteinte';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                return ' - Inadéquation des modes ou underflow';
                break;
            case JSON_ERROR_CTRL_CHAR:
                return ' - Erreur lors du contrôle des caractères';
                break;
            case JSON_ERROR_SYNTAX:
                return ' - Erreur de syntaxe ; JSON malformé';
                break;
            case JSON_ERROR_UTF8:
                return ' - Caractères UTF-8 malformés, probablement une erreur d\'encodage';
                break;
            default:
                return ' - Erreur inconnue';
                break;
        }
    }

}
