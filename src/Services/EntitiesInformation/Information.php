<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Services\EntitiesInformation;

class Information extends \obo\Object {

    /** @var array */
    protected $modelsDirs = [];
    /** @var \obo\Services\EntitiesInformation\Explorer */
    protected $explorer = null;
    /** @var \obo\Interfaces\ICache */
    protected $cache = null;
    /** @var \obo\Carriers\EntityInformationCarrier[] */
    protected $entitiesInformations = [];
    /** @var bool */
    protected $cacheValidity = true;
    /** @var string */
    protected $lockFilePath = "";

    /**
     * @param array $modelsDirs
     * @param \obo\Services\EntitiesInformation\Explorer $explorer
     * @param \obo\Interfaces\ICache $cache
     */
    public function __construct(array $modelsDirs, \obo\Services\EntitiesInformation\Explorer $explorer, \obo\Interfaces\ICache $cache) {
        $this->explorer = $explorer;
        $this->cache = $cache;
        $this->modelsDirs = $modelsDirs;
        $this->lockFilePath = \obo\obo::tempDir() . "/cache.lock";
        if (\obo\obo::$developerMode) $this->validateCache();
    }

    /**
     * @param string $className
     * @return \obo\Carriers\EntityInformationCarrier
     */
    public function informationForEntityWithClassName($className) {
       $className = \ltrim($className, "\\");
       return isset($this->entitiesInformations[$className]) ? $this->entitiesInformations[$className] : $this->loadClassInformationForEntityWithClassName($className);
    }

    /**
     * @return array
     * @throws \obo\Exceptions\Exception
     */
    public function entitiesInformations() {
        $entitiesInformations = [];

        if (\is_file($this->lockFilePath)) {
            $fp = \fopen($this->lockFilePath, "c+" );
            if (!\flock($fp, \LOCK_EX)) throw new \obo\Exceptions\Exception("Unable to acquire exclusive lock");
            $this->validateCache();
            \flock($fp, \LOCK_UN);
            \fclose($fp);
        }

        if (($entitiesList = $this->cache->load("entitiesList")) === null) {
            $this->createCache();
            if (($entitiesList = $this->cache->load("entitiesList")) === null) throw new \obo\Exceptions\Exception("Failed to load cache entities information. Possible cause could be that you can't write to the cache folder or folders with all models are not loaded");
        }

        foreach($entitiesList as $entityClassName) $entitiesInformations[$entityClassName] = $this->informationForEntityWithClassName($entityClassName);

        return $entitiesInformations;
    }

    /**
     * @return void
     */
    protected function validateCache() {
        $this->cacheValidity = $this->cache->load("changesHash") === $this->calculateChangesHash();
    }

    /**
     * @return string
     */
    protected function calculateChangesHash() {
        $lastChange = "";

        foreach ($this->modelsDirs as $dir) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveDirectoryIterator::CURRENT_AS_FILEINFO);
            $files = new \RegexIterator($iterator, '#^.+\.php$#', \RegexIterator::MATCH, \RegexIterator::USE_KEY);

            foreach ($files as $file) {
                if ($lastChange < $changeTime = \filemtime($file)) $lastChange = $changeTime;
            }
        }

        return \md5($lastChange);
    }

    /**
     * @return void
     * @throws \obo\Exceptions\Exception
     */
    public function createCache() {
        $entitiesList = [];
        $fp = \fopen($this->lockFilePath, "c+" );
        if (!\flock($fp, LOCK_EX)) throw new \obo\Exceptions\Exception("Unable to acquire exclusive lock");

        foreach ($this->explorer->analyze($this->modelsDirs) as $className => $entityInformation) {
            $this->cache->store($className, $entityInformation);
            $entitiesList[] = $className;
        }

        $this->cache->store("entitiesList", $entitiesList);
        $this->cache->store("changesHash", $this->calculateChangesHash());
        $this->cacheValidity = true;
        \flock($fp, \LOCK_UN);
        \fclose($fp);
        \unlink($this->lockFilePath);
    }

    /**
     * @param string $className
     * @return \obo\Carriers\EntityInformationCarrier
     * @throws \obo\Exceptions\Exception
     */
    protected function loadClassInformationForEntityWithClassName($className) {

        if (\is_file($this->lockFilePath)) {
            $fp = \fopen($this->lockFilePath, "c+" );
            if (!\flock($fp, \LOCK_EX)) throw new \obo\Exceptions\Exception("Unable to acquire exclusive lock");
            $this->validateCache();
            \flock($fp, \LOCK_UN);
            \fclose($fp);
        }

        if (!$this->cacheValidity OR ($entityInformation = $this->cache->load($className)) === null) {
            $this->createCache();
            if (($entityInformation = $this->cache->load($className)) === null) throw new \obo\Exceptions\Exception ("Failed to load cache entities information. Possible cause could be that you can't write to the cache folder or folders with all models are not loaded");
        } else {
            $entityInformation = $this->cache->load($className);
        }

        $this->registerRunTimeEventsForEntity($entityInformation);
        return $this->entitiesInformations[\ltrim($className, "\\")] = $entityInformation;
    }

    /**
     * @param \obo\Carriers\EntityInformationCarrier $entityInformation
     */
    private function registerRunTimeEventsForEntity(\obo\Carriers\EntityInformationCarrier $entityInformation) {
        foreach ($entityInformation->annotations as $annotation) $annotation->registerEvents();

        foreach ($entityInformation->propertiesInformation as $propertyInformation) {
            foreach ($propertyInformation->annotations as $annotation) $annotation->registerEvents();
            if ($propertyInformation->dataType instanceof \obo\DataType\Base\DataType) $propertyInformation->dataType->registerEvents();
        }

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => $entityInformation->className,
            "name" => "beforeChange".\ucfirst($entityInformation->primaryPropertyName),
            "actionAnonymousFunction" => function($arguments) {
                if ($arguments["entity"]->isInitialized()) {
                    $backTrace = \debug_backtrace();
                    if ($backTrace[4]["function"] !== "insertEntity") throw new \obo\Exceptions\PropertyAccessException("Primary entity property can't be changed, has been marked as initialized");
                }},
        ]));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => $entityInformation->className,
            "name" => "beforeChange",
            "actionAnonymousFunction" => function($arguments) {
                if ($arguments["entity"]->isDeleted()) throw new \obo\Exceptions\PropertyAccessException("Property '{$arguments["propertyName"]}' can't be changed, entity '{$arguments["entity"]->entityInformation()->className}' is deleted");
            },
        ]));
    }
}
