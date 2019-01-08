<?php
namespace EveryCheck\TestApiRestBundle\Matcher;

use Coduo\PHPMatcher\Factory;

class Matcher implements MatcherInterface
{

    protected $matcher;

    public function __construct()
    {
        $this->matcher = (new Factory\SimpleFactory())->createMatcher();
    }

    public function match($a, $b): bool
    {
        return $this->matcher->match($a, $b);
    }

    public function getError(): string
    {
        return $this->matcher->getError();
    }
}