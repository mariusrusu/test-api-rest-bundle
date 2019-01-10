<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\PatternBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;

/**
 * Demo
 *
 * @ORM\Table(name="pattern")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity()
 */
class Pattern
{
    /**
     * @var \Ramsey\Uuid\Uuid
     *
     * @ORM\Column(type="uuid", unique=true)
     * @JMS\Accessor(getter="getUuidAsString")
     */
    private $uuid;

    /**
     * IMPORTANT! This field annotation must be the last one in order to prevent
     * that Doctrine will use UuidGenerator as $`class->idGenerator`!
     *
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="value", type="integer")
     */
    private $value;


    /**
     * Expiry date to login
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_creation", type="datetime")
     *
     */
    private $dateOfCreation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false, options={"default" : 1})
     */
    private $active = true;

    public function __construct()
    {
        $this->setDateOfCreation(new \DateTime('now'));
    }

    public function getid()
    {
        return $this->id;
    }

    /**
     * Get uuid
     *
     * @return \Ramsey\Uuid\Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuidAsString()
    {
        return $this->uuid->toString();
    }


    /**
     * Set uuid
     *
     * @return Study
     */
    public function setUuid(\Ramsey\Uuid\Uuid $uuid )
    {
        $this->uuid = $uuid;
        return $this;
    }


    /**
     * @ORM\PrePersist
     */
    public function setupUuid()
    {
        $this->setUuid(\Ramsey\Uuid\Uuid::uuid4());
        return $this;
    }


    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Demo
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value.
     *
     * @param int $value
     *
     * @return Demo
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return \DateTime
     */
    public function getDateOfCreation()
    {
        return $this->dateOfCreation;
    }

    /**
     * @param \DateTime $dateOfCreation
     */
    public function setDateOfCreation($dateOfCreation)
    {
        $this->dateOfCreation = $dateOfCreation;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }


}
