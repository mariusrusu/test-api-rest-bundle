<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\tests\SampleProjectBisBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\SampleProjectBundle\Entity\Demo;

class LoadDemoFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $demo1 = new Demo();
        $demo1->setName("bis");
        $demo1->setValue(312);
        $manager->persist($demo1);

        $manager->flush();
    }
}