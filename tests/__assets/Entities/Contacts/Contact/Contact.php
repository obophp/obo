<?php

namespace obo\Tests\Assets\Entities\Contacts;

/**
 * @obo-name(Contact)
 * @obo-repositoryName(TestsEntitiesContactsContact)
 * @property string $name
 * @property \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone[] $phones
 * @property \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email[] $emails
 * @property \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Url[] $urls
 * @property \obo\Tests\Assets\Entities\Contacts\Address[] $addresses
 * @property \obo\Tests\Assets\Entities\Contacts\Address $defaultAddress
 * @property \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone $defaultPhone
 * @property \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email $defaultEmail
 * @property \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email $administrativeEmail
 * @property string $note
 * @property string $noPersitedProperty
 */
class Contact extends \obo\Tests\Assets\AbstractEntities\Contacts\Contact {

    /**
     * @obo-run(afterConnectToOwner)
     * @param array $event
     */
    public function conectOwner($event) {
        if ($event["owner"] instanceof AdditionalInformation || $event["owner"] instanceof Address) return;
        if ($event["owner"]->isBasedInRepository()) {
            $this->owner = $event["owner"];
        } else {
            \obo\obo::$eventManager->registerEvent(
                new \obo\Services\Events\Event([
                    "onObject" => $event["owner"],
                    "name" => "afterInsert",
                    "actionAnonymousFunction" => function($arguments) {
                        $arguments["contact"]->setValueForPropertyWithName($arguments["entity"], "owner");
                        $arguments["contact"]->save();
                    },
                    "actionArguments" => ["contact" => $this],
                ]));
        }
    }

    /**
     * @obo-run(afterDisconnectFromOwner)
     * @param array $event
     */
    public function disconectOwner($event) {
        if ($event["owner"] instanceof AdditionalInformation || $event["owner"] instanceof Address) return;
        if ($event["owner"]->isBasedInRepository()) {
            if (!$this->isDeleted()) {
                $this->owner = null;
            }
        } else {
            \obo\obo::$eventManager->registerEvent(
                new \obo\Services\Events\Event([
                    "onObject" => $event["owner"],
                    "name" => "afterInsert",
                    "actionAnonymousFunction" => function($arguments) {
                        $arguments["contact"]->setValueForPropertyWithName(null, "owner");
                        $arguments["contact"]->save();
                    },
                    "actionArguments" => ["contact" => $this],
                ]));
        }
    }

}
