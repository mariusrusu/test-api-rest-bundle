<?php

namespace EveryCheck\TestApiRestBundle\MultipartParser;

use EveryCheck\TestApiRestBundle\MultipartParser\Parser\MultipartParser;

/**
 * Facade for parsing and selecting the right Multipart Parser.
 */
class ParserSelector
{
    /**
     * Give me your Content-Type, and i give you a parser.
     *
     * @param $contentType
     * @return MultipartParser|null
     */
    public function getParserForContentType($contentType)
    {
        if(0 !== stripos($contentType, 'multipart/')) {
            return null;
        }

        list($mime, $boundary) = $this->parseContentType($contentType);

        $parser = new MultipartParser();
        $parser->setBoundary($boundary);

        return $parser;
    }

    /**
     * Helper or parsing the Content-Type.
     *
     * @param $contentType
     * @return array
     * @throws ParserException
     */
    protected function parseContentType($contentType)
    {
        if(false === stripos($contentType, ';')) {
            throw new ParserException('ContentType does not contain a \';\'');
        }

        list($mime, $boundary) = explode(';', $contentType, 2);
        list($key, $boundaryValue) = explode('=', trim($boundary), 2);

        if('boundary' != $key) {
            throw new ParserException('Boundary does not start with \'boundary=\'');
        }

        return [strtolower(trim($mime)), $boundaryValue];
    }
} 