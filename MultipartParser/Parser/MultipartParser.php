<?php

namespace EveryCheck\TestApiRestBundle\MultipartParser\Parser;

use EveryCheck\TestApiRestBundle\MultipartParser\ParserException;

/**
 * Default parser for multipart syntax.
 */
class MultipartParser extends AbstractParser
{
    public function parse($content)
    {
        $bodies = explode('--'.$this->getBoundary(), $content);

        // RFC says, to ignore preamble and epiloque.
        $preamble = array_shift($bodies);
        $epilogue = array_pop($bodies);

        // Need to check the first chars of epiloque, because of explode().
        if (0 !== stripos($epilogue ,"--" . static::EOL)) {
            throw new ParserException('Boundary end did not match');
        }

        $bodies = $this->parseBodies($bodies);

        foreach($bodies as $i => $body) {
            // RFC says, no content type means text/plain.
            if(!isset($body['headers']['content-type'])) {
                $bodies[$i]['headers']['content-type'][] = 'text/plain';
            }
        }

        return $bodies;
    }
}