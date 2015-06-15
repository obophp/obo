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
    const _VERSION = "development";
    const _LICENCE = "Apache License, Version 2.0";
    const _WWW = "http://www.obophp.org/";

    const ENTITIES_EXPLORER = "obo-entitiesExplorer";
    const ENTITIES_INFORMATION = "obo-entitiesInformation";
    const IDENTITY_MAPPER = "obo-identityMapper";
    const EVENT_MANAGER = "obo-eventManager";
    const DEFAULT_DATA_STORAGE = "obo-defaultDataStorage";
    const CACHE = "obo-cache";
    const UUID_GENERATOR = "obo-uuidGenerator";

    /**
     * @var boolean
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
     * @return void
     */
    public static function setDefaultDataStorage(\obo\Interfaces\IDataStorage $defaultDataStorage) {
        \obo\Services::registerServiceWithName($defaultDataStorage, self::DEFAULT_DATA_STORAGE);
    }

    /**
     * @return void
     */
    public static function setCache(\obo\Interfaces\ICache $cache) {
        \obo\Services::registerServiceWithName($cache, self::CACHE);
    }

    /**
     * @param \obo\Interfaces\IUuidGenerator $uuidGenerator
     * @return void
     */
    public static function setUuidGenerator(\obo\Interfaces\IUuidGenerator $uuidGenerator) {
        \obo\Services::registerServiceWithName($uuidGenerator, self::UUID_GENERATOR);
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
     * @return type
     */
    public static function tempDir() {
        return self::$tempDir;
    }

    /**
     * @return void
     */
    public static function run() {
        self::checkConfiguration();
        \obo\Services::registerServiceWithName(new \obo\Services\Events\EventManager, self::EVENT_MANAGER);
        \obo\Services::registerServiceWithName(new \obo\Services\EntitiesInformation\Explorer(), self::ENTITIES_EXPLORER);
        \obo\Annotation\CoreAnnotations::register(\obo\Services::serviceWithName(self::ENTITIES_EXPLORER));
        \obo\Services::registerServiceWithName(new \obo\Services\EntitiesInformation\Information(self::$modelsDirs, \obo\Services::serviceWithName(self::ENTITIES_EXPLORER), \obo\Services::serviceWithName(self::CACHE)), self::ENTITIES_INFORMATION);
        \obo\Services::registerServiceWithName(new \obo\Services\IdentityMapper\IdentityMapper, self::IDENTITY_MAPPER);
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
