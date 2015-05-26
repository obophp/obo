<?php

namespace Obo;

class Cache implements \obo\Interfaces\ICache {

    private $cacheEngine = null;


    public function __construct($cacheTemporary) {
        $this->cacheEngine = new \Nette\Caching\Cache(new \Nette\Caching\Storages\FileStorage($cacheTemporary));
    }

    public function load($key) {
        return $this->cacheEngine->load($key);
    }

    public function store($key, $value) {
        return $this->cacheEngine->save($key, $value);
    }
}
