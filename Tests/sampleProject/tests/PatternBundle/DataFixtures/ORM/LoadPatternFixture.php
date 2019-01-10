<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\tests\PatternBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\PatternBundle\Entity\Pattern;

class LoadPatternFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $pattern1 = new Pattern();
        $pattern1->setName("foo");
        $pattern1->setValue(300);
        $manager->persist($pattern1);
        $pattern1->setUuid(\Ramsey\Uuid\Uuid::fromString("03d00000-0000-4000-a000-000000000001"));

        $pattern2 = new Pattern();
        $pattern2 ->setName("the answer");
        $pattern2 ->setValue(42);
        $manager->persist($pattern2 );

        $manager->flush();
    }
}