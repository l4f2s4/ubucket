<?php

namespace GitStash\Git;

class Object2 {

    protected $hash;

    public function isBlob()
    {
        return false;
    }

    public function isTag()
    {
        return false;
    }

    public function isCommit()
    {
        return false;
    }

    public function isTree()
    {
        return false;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }
}