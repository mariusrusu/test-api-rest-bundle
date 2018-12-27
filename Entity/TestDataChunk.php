<?php

namespace EveryCheck\TestApiRestBundle\Entity;


class TestDataChunk implements \Iterator
{
    const KIND_UNIT_TEST = 'KIND_UNIT_TEST';
    const KIND_SCENARIO = 'KIND_SCENARIO'; 
    const KIND_DATABASE = 'KIND_DATABASE'; 

    // $environmentVariables = [];
    private $position = 0;

    public function __construct($kind,$filename)
    {
        $this->data = [];
        $this->filename = $filename;
        $this->kind = $kind;
        $this->position = 0;
    }


    public function setData($data)
    {
        $this->data = $data;
        $this->obj = new \ArrayObject( $data );
        $this->it = $this->obj->getIterator();
    }

    public function rewind() 
    {
        return $this->it->rewind();
    }

    public function current() 
    {
        return $this->it->current();
    }

    public function key()
    {
        return $this->it->key();
    }

    public function next() 
    {
        return $this->it->next();
    }

    public function valid()
    {
        return $this->it->valid();
    }

}

