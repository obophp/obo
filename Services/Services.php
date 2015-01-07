<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo;

class Services extends \obo\Object {

    /**
     * @var array
     */
    private static $services = array();

    /**
     * @var array
     */
    private static $factories = array();

    /**
     * @param mixed $service
     * @param string $serviceName
     * @param boolean $forced
     * @return void
     */
    public static function registerServiceWithName($service, $serviceName, $forced = false) {
        if ($forced === true AND isset(self::$services[$serviceName])) throw new \obo\Exceptions\ServicesException("Service with name '{$serviceName}' is also registered");
        self::$services[$serviceName] = $service;
    }

    /**
     * @param function $factory
     * @param string $serviceName
     * @param boolean $forced
     * @return void
     */
    public static function registerFactoryForServiceWithName($factory, $serviceName, $forced = false) {
        if ($forced === true AND isset(self::$factories[$serviceName])) throw new \obo\Exceptions\ServicesException("Factory for service with name '{$serviceName}' is also registered");
        self::$factories[$serviceName] = $factory;
    }

    /**
     * @param string $serviceName
     * @return mixed
     */
    public static function serviceWithName($serviceName) {
        if (isset(self::$services[$serviceName])) {
            return self::$services[$serviceName];
        } elseif(isset(self::$factories[$serviceName])) {
            $factory = self::$factories[$serviceName];
            return self::$services[$serviceName] = $factory();
        }
        throw new \obo\Exceptions\ServicesException("Service with name '{$serviceName}' is not registered");
    }

    /**
     * @param string $serviceName
     * @return boolean
     */
    public static function isRegisteredServiceWithName($serviceName) {
        return isset(self::$services[$serviceName]) OR isset(self::$factories[$serviceName]);
    }

}