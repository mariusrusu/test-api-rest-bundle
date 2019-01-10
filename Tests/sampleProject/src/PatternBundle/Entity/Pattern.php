<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\PatternBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Demo
 *
 * @ORM\Table(name="pattern")
 * @ORM\Entity()
 */
class Pattern
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
