<?php

namespace EveryCheck\TestApiRestBundle\MultipartParser;

interface ParserInterface
{
    /** Newline as told by RFC 1341 */
    const EOL = "\r\n";

    /**
     * @param $boundary string Delimiter between multipart bodies.
     * @return void
     */
    public function setBoundary($boundary);

    /**
     * Parses the multipart content to a array structure.
     *
     * @param $content
     * @return array
     */
    public function parse($content);
} 