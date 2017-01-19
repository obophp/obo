<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo;

class obo extends \obo\Object {

    const _NAME = "obo";
    const _VERSION = "v0.14-dev";
    const _VERSION_ID = 1400;
    const _LICENCE = "Apache License, Version 2.0";
    const _WWW = "http://www.obophp.org/";

    /**
     * @var bool
     */
    public static $developerMode = false;

    /**
     * @var array
     */
    protected static $modelsDirs = [];

    /**
     * @var string
     */
    protected static $tempDir = "";

    /**
     * @var \obo\Services\EntitiesInformation\Explorer
     */
    public static $entitiesExplorer = null;

    /**
     * @var \obo\Services\EntitiesInformation\Information
     */
    public static $entitiesInformation = null;

    /**
     * @var \obo\Services\IdentityMapper\IdentityMapper
     */
    public static $identityMapper = null;

    /**
     * @var \obo\Services\Events\EventManager
     */
    public static $eventManager = null;

    /**
     * @var \obo\Interfaces\IDataStorage
     */
    public static $defaultDataStorage = null;

    /**
     * @var \obo\Interfaces\ICache
     */
    public static $cache = null;

    /**
     * @var \obo\Interfaces\IUuidGenerator
     */
    public static $uuidGenerator = null;

    /**
     * @param \obo\Interfaces\IDataStorage $defaultDataStorage
     * @return void
     */
    public static function setDefaultDataStorage(\obo\Interfaces\IDataStorage $defaultDataStorage) {
        self::$defaultDataStorage = $defaultDataStorage;
    }

    /**
     * @param \obo\Interfaces\ICache $cache
     * @return void
     */
    public static function setCache(\obo\Interfaces\ICache $cache) {
        self::$cache = $cache;
    }

    /**
     * @param \obo\Interfaces\IUuidGenerator $uuidGenerator
     * @return void
     */
    public static function setUuidGenerator(\obo\Interfaces\IUuidGenerator $uuidGenerator) {
        self::$uuidGenerator = $uuidGenerator;
    }

    /**
     * @param array $modelsDirs
     * @return void
     */
    public static function addModelsDirs(array $modelsDirs) {
        self::$modelsDirs = \array_merge(self::$modelsDirs, $modelsDirs);
    }

    /**
     * @param string $tempDir
     */
    public static function setTempDir($tempDir) {
        self::$tempDir = $tempDir;
    }

    /**
     * @return string
     */
    public static function tempDir() {
        return self::$tempDir;
    }

    /**
     * @return void
     */
    public static function run() {
        self::checkConfiguration();
        self::$identityMapper = new \obo\Services\IdentityMapper\IdentityMapper();
        self::$eventManager = new \obo\Services\Events\EventManager();
        self::$entitiesExplorer = new \obo\Services\EntitiesInformation\Explorer();

        \obo\Annotation\CoreAnnotations::register(self::$entitiesExplorer);
        \obo\DataType\CoreDataTypes::register(self::$entitiesExplorer);

        self::$entitiesInformation = new \obo\Services\EntitiesInformation\Information(self::$modelsDirs, self::$entitiesExplorer, self::$cache);
    }

    /**
     * @return void
     * @throws \obo\Exceptions\Exception
     */
    public static function checkConfiguration() {
        if (!count(self::$modelsDirs)) throw new \obo\Exceptions\Exception("Obo can't run, path for models is not defined");
        if (self::$tempDir === "") throw new \obo\Exceptions\Exception("Obo can't run, path for temp dir is not defined");
    }

}
