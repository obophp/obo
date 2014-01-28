<?php

/** 
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Services\EntitiesInformation;

class Information extends \obo\Object {
    
    /** @var \obo\Carriers\EntityInformationCarrier[] */
    private $entitiesInformations = array(); 
    
    /**
     * @param string $className
     * @return \obo\Carriers\EntityInformationCarrier 
     */
    public function informationForEntityWithClassName($className) {
        if(!isset($this->entitiesInformations[$className])) $this->findClassInformationForEntityWithClassName($className);
        return $this->entitiesInformations[$className];        
    }

    /**
     * @param string $className
     * @return \obo\Carriers\EntityInformationCarrier 
     */  
    private function findClassInformationForEntityWithClassName($className) {
        
        if (\obo\obo::$developerMode === false AND \obo\Services::isRegisteredServiceWithName(\obo\obo::CACHE)) {
            $cache = \obo\Services::serviceWithName(\obo\obo::CACHE);
            $entityInformation = $cache->load($className);
            if($entityInformation === NULL) {
                $entityInformation = \obo\Services::serviceWithName(\obo\obo::ENTITIES_EXPLORER)->exploreEntityWithName($className);
                if ($entityInformation->repositoryColumns !== false) $cache->store($className, $entityInformation);
            }
        } else {
            $entityInformation = \obo\Services::serviceWithName(\obo\obo::ENTITIES_EXPLORER)->exploreEntityWithName($className);
        }
        
        $this->registerCoreEventsForEntity($entityInformation);
        
        if ($entityInformation->repositoryColumns !== false) $this->entitiesInformations[$className] = $entityInformation;
        
        return $entityInformation;
    }  
    
    /**
     * @param \obo\Carriers\EntityInformationCarrier $entityInformation
     * @return void
     */
    private function registerCoreEventsForEntity(\obo\Carriers\EntityInformationCarrier $entityInformation) {
            
            foreach($entityInformation->annotations as $annotation) $annotation->registerEvents();
            
            foreach($entityInformation->propertiesInformation as $propertyInformation) {
                foreach($propertyInformation->annotations as $annotation) {
                    $annotation->registerEvents();
                }
                
                if($propertyInformation->dataType instanceof \obo\DataType\Base\DataType) $propertyInformation->dataType->registerEvents();
            }

            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
                "onClassWithName" => $entityInformation->className,
                "name" => "beforeChange".\ucfirst($entityInformation->primaryPropertyName),
                "actionAnonymousFunction" => function($arguments) {
                    if ($arguments["entity"]->isInitialized()) {
                        $backTrace = \debug_backtrace();
                        if ($backTrace[4]["function"] !== "insertEntityToRepository") throw new \obo\Exceptions\PropertyAccessException("Primary entity property can not changed, the entity was marked as initialized");
                    }},
                )
            ));
    }
    
}