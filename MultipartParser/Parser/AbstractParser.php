<?php

namespace EveryCheck\TestApiRestBundle\MultipartParser\Parser;

use EveryCheck\TestApiRestBundle\MultipartParser\ParserInterface;

/**
 * Some reuseable default functionality for multipart parser.
 */
abstract class AbstractParser implements ParserInterface
{
    protected $boundary;

    public function setBoundary($boundary)
    {
        $this->boundary = $boundary;
    }

    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * Will parse a list of [header-]newline-body string.
     *
     * @param array $bodies
     * @return array
     */
    protected function parseBodies(array $bodies)
    {
        $parseBodies = [];

        foreach($bodies as $body) {
            $parseBodies[] = $this->parseBody($body);
        }

        return $parseBodies;
    }

    /**
     * Will parse a single [header-]newline-body string.
     *
     * @param $body
     * @return array
     */
    protected function parseBody($body)
    {
        // Headers come first, then content.
        $isHeader = true;
        $headers = [];
        $content = [];

        foreach (explode(static::EOL, $body) as $i => $line) {

            if (0 == $i) {
                // Skip the first line
                continue;
            }

            if ('' == trim($line)) {
                // First newline starts body in next line.
                $isHeader = false;
                continue;
            }

            if ($isHeader) {
                list($header, $value) = explode(':', $line);
                if ($header) {
                    $headers[strtolower($header)][] = trim($value);
                }
            } else {
                $content[] = $line;
            }
        }

        return ['headers' => $headers, 'body' => implode(static::EOL, $content)];
    }
}
