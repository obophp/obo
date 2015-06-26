<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Services\Events;

class EventManager extends \obo\Object {

    /**
     * @var array
     */
    private $events = [];

    /**
     * @var array
     */
    private $ignoreEvents = [];

    /**
     * @var array
     */
    private $registeredEntities = [];

    /**
     * @param \obo\Services\Events\Event $event
     * @return void
     */
    public function registerEvent(\obo\Services\Events\Event $event) {
        $eventIdentificationKey = $event->eventIdentificationKey();
        if (!isset($this->events[$eventIdentificationKey])) $this->events[$eventIdentificationKey] = [];
        $this->events[$eventIdentificationKey][] = $event;
    }

    /**
     * @param string $eventName
     * @param \obo\Entity $entity
     * @param array $arguments
     * @return void
     * @throws \Exception
     */
    public function notifyEventForEntity($eventName, \obo\Entity $entity, array $arguments = []) {
        if ($this->isRegisteredEntity($entity)) {
            $objectEventKey = $eventName.$entity->objectIdentificationKey();
            $classEventKey =  $eventName.$entity->className();

            if (isset($this->events[$objectEventKey]) AND !$this->isActiveIgnoreNotifyForEventWithKey($objectEventKey)) {
                try {
                    $this->addIgnoreNotifyForEventWithKey($objectEventKey);
                    foreach ($this->events[$objectEventKey] as $event) $this->executeAction ($event, $entity, $arguments);
                    $this->removeIgnoreNotifyForEventWithKey($objectEventKey);
                } catch (\Exception $e) {
                    $this->removeIgnoreNotifyForEventWithKey($objectEventKey);
                    throw $e;
                }
            }

            if (isset($this->events[$classEventKey]) AND !$this->isActiveIgnoreNotifyForEventWithKey($objectEventKey)) {
                try {
                    $this->addIgnoreNotifyForEventWithKey($objectEventKey);
                foreach ($this->events[$classEventKey] as $event) $this->executeAction ($event, $entity, $arguments);
                $this->removeIgnoreNotifyForEventWithKey($objectEventKey);
                } catch (\Exception $e) {
                    $this->removeIgnoreNotifyForEventWithKey($objectEventKey);
                    throw $e;
                }
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
        if ($event->actionAnonymousFunction !== null) {
            $arguments = \array_merge ($event->actionArguments, $arguments, ["entity" => $entity]);
            $actionAnonymousFunction = $event->actionAnonymousFunction;
            $actionAnonymousFunction($arguments);
        } else {
            $message = $event->actionMessage;
            $arguments = \array_merge ($event->actionArguments, $arguments);
            if ($event->actionEntity === null) $entity->$message($arguments); else $event->actionEntity->$message($arguments);
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
     * @return bool
     */
    public function isActiveIgnoreNotifyForEventWithKey($eventKey) {
        return isset($this->ignoreEvents[$eventKey]);
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function registerEntity(\obo\Entity $entity) {
        $this->registeredEntities[$entity->objectIdentificationKey()] = true;
    }

    /**
     * @param \obo\Entity $entity
     * @return bool
     */
    public function isRegisteredEntity(\obo\Entity $entity) {
        return isset($this->registeredEntities[$entity->objectIdentificationKey()]);
    }

}
