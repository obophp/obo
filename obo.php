<?php

/** 
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo;

class obo extends \obo\Object {

    const _NAME = "obo";
    const _VERSION = "development";
    const _AUTHOR = "Adam Suba";
    const _LICENCE = "Apache License, Version 2.0";
    const _WWW = "http://www.obophp.org/";
    
    const ENTITIES_EXPLORER = "obo-entitiesExplorer";
    const ENTITIES_INFORMATION = "obo-entitiesInformation";
    const IDENTITY_MAPPER = "obo-identityMapper";
    const EVENT_MANAGER = "obo-eventManager";
    const REPOSITORY_MAPPER = "obo-repositoryMapper";
    const REPOSITORY_LAYER = "obo-repositoryLayer";
    const CACHE = "obo-cache";
    
    public static $developerMode = false;
    
    /**
     * @param \DibiConnection $repositoryLayer 
     */
    public static function connectToRepositoryLayer(\DibiConnection $repositoryLayer) {
        \obo\Services::registerServiceWithName($repositoryLayer, self::REPOSITORY_LAYER);
    }
    
    public static function setCache(\obo\Interfaces\ICache $cache) {
        \obo\Services::registerServiceWithName($cache, self::CACHE);
    }
    
    /**
     * @return void 
     */
    public static function run() {
        \obo\Services::registerServiceWithName(new \obo\Services\EntitiesInformation\Explorer(), self::ENTITIES_EXPLORER);
        \obo\Services::registerServiceWithName(new \obo\Services\EntitiesInformation\Information(), self::ENTITIES_INFORMATION);
        \obo\Services::registerServiceWithName(new \obo\Services\IdentityMapper\IdentityMapper, self::IDENTITY_MAPPER);
        \obo\Services::registerServiceWithName(new \obo\Services\Events\EventManager, self::EVENT_MANAGER);
        \obo\Services::registerServiceWithName(new \obo\RepositoryMappers\DibiRepositoryMapper, self::REPOSITORY_MAPPER);
        
        \obo\Annotation\CoreAnnotations::register(\obo\Services::serviceWithName(self::ENTITIES_EXPLORER));
    }
}