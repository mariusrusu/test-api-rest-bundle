<?php
namespace EveryCheck\TestApiRestBundle\Matcher;

interface MatcherInterface
{
    public function match($a, $b): bool;

    public function getError(): string ;
}