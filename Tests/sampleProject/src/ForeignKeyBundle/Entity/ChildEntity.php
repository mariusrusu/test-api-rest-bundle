<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChildEntity
 *
 * @ORM\Table(name="child_entity")
 * @ORM\Entity()
 */
class ChildEntity
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
     * @ORM\ManyToOne(targetEntity="ParentEntity", inversedBy="children")
     */
    private $parent;

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
     * Set parent.
     *
     * @param string $parent
     *
     * @return ParentEntity
     */
    public function setParent(ParentEntity $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent.
     *
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }
}
