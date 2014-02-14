<?php

/**

 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Services\Events;

class EventManager extends \obo\Object{
    private $events = array();
    private $ignoreEvents = array();
    private $ignoreNotificationEntities = array();

    /**
     * @param \obo\Services\Events\Event $event

     * @return void
     */
    public function registerEvent(\obo\Services\Events\Event $event) {
        $eventIdentificationKey = $event->eventIdentificationKey();
        if (!isset($this->events[$eventIdentificationKey])) $this->events[$eventIdentificationKey] = array();
        $this->events[$eventIdentificationKey][] = $event;
    }

    /**
     * @param string $eventName
     * @param \obo\Entity $entity
     * @param array $arguments
     * @return void

     */
    public function notifyEventForEntity($eventName, \obo\Entity $entity, array $arguments = array()) {
        if (!$this->isActiveIgnoreNotificationForEntity($entity)) {
            $objectEventKey = $eventName.$entity->objectIdentificationKey();
            $classEventKey =  $eventName.$entity->className();

            if (isset($this->events[$objectEventKey]) AND !$this->isActiveIgnoreNotifyForEventWithKey($objectEventKey)) {
                $this->addIgnoreNotifyForEventWithKey($objectEventKey);
                foreach ($this->events[$objectEventKey] as $event) $this->executeAction ($event, $entity, $arguments);
                $this->removeIgnoreNotifyForEventWithKey($objectEventKey);
            }

            if (isset($this->events[$classEventKey]) AND !$this->isActiveIgnoreNotifyForEventWithKey($classEventKey)) {
                $this->addIgnoreNotifyForEventWithKey($classEventKey);
                foreach ($this->events[$classEventKey] as $event) $this->executeAction ($event, $entity, $arguments);
                $this->removeIgnoreNotifyForEventWithKey($classEventKey);
            }

        }
    }

    /**
     * @param \obo\Services\Events\Event $event
     * @param \obo\Entity $entity
     * @param array $arguments
     * @return void

     */
    private function executeAction(\obo\Services\Events\Event $event, \obo\Entity $entity, array $arguments) {
        if (!is_null($event->actionAnonymousFunction)) {
            $arguments = \array_merge ($event->actionArguments, $arguments, array("entity" => $entity));
            $actionAnonymousFunction = $event->actionAnonymousFunction;
            $actionAnonymousFunction($arguments);
        } else {
            $message = $event->actionMessage;
            $arguments = \array_merge ($event->actionArguments, $arguments);
            if (is_null($event->actionEntity)) $entity->$message($arguments); else $event->actionEntity->$message($arguments);
        }
    }

    /**
     * @param string $eventKey

     * @return void
     */
    public function addIgnoreNotifyForEventWithKey($eventKey) {
        $this->ignoreEvents[$eventKey] = true;
    }

    /**
     * @param string $eventKey

     * @return void
     */
    public function removeIgnoreNotifyForEventWithKey($eventKey) {
        unset ($this->ignoreEvents[$eventKey]);
    }

    /**
     * @param string $eventKey
     * @return boolean

     */
    public function isActiveIgnoreNotifyForEventWithKey($eventKey) {
        return isset($this->ignoreEvents[$eventKey]);
    }

    /**
     * @param \obo\Entity $entity

     * @return void
     */
    public function turnOnIgnoreNotificationForEntity(\obo\Entity $entity) {
        $this->ignoreNotificationEntities[$entity->objectIdentificationKey()] = true;;
    }

    /**
     * @param \obo\Entity $entity

     * @return void
     */
    public function turnOffIgnoreNotificationForEntity(\obo\Entity $entity) {
        unset($this->ignoreNotificationEntities[$entity->objectIdentificationKey()]);
    }

    /**
     * @param \obo\Entity $entity

     * @return boolean

     */
    public function isActiveIgnoreNotificationForEntity(\obo\Entity $entity) {
        return isset($this->ignoreNotificationEntities[$entity->objectIdentificationKey()]);
    }
}