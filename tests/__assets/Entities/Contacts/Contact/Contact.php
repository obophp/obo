<?php

namespace obo\Tests\Assets\Entities\Contacts;

/**
 * @obo-repositoryName(TestsEntitiesContactsContact)
 * @property string $name
 * @property \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone[] $phones
 * @property \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email[] $emails
 * @property \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Url[] $urls
 * @property \obo\Tests\Assets\Entities\Contacts\Address[] $addresses
 * @property \obo\Tests\Assets\Entities\Contacts\Address $defaultAddress
 * @property \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone $defaultPhone
 * @property \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email $defaultEmail
 * @property string note
 */
class Contact extends \obo\Tests\Assets\Entities\Entity {

    /**
     * @obo-run(afterAddToAddresses)
     * @param array $event
     */
    public function controlDefaultAddress($event) {
        if (\count($this->addresses) === 1) {
            if ($this->isBasedInRepository()) {
                $this->defaultAddress = $this->addresses->rewind();
            } else {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
                        new \obo\Services\Events\Event([
                    "onObject" => $this,
                    "name" => "afterInsert",
                    "actionAnonymousFunction" => function($arguments) {
                        if ($arguments["entity"]->valueForPropertyWithName("defaultAddress", false, false)) return;
                        $arguments["entity"]->setValueForPropertyWithName($arguments["defaultAddress"], "defaultAddress");
                        $arguments["entity"]->save();
                    },
                    "actionArguments" => ["defaultAddress" => $this->addresses->rewind()],
                ]));
            }
        }
    }

    /**
     * @obo-run(afterAddToEmails)
     * @param array $event
     */
    public function controlDefaultEmail($event) {
        if (\count($this->emails) === 1) {
            if ($this->isBasedInRepository()) {
                $this->defaultEmail = $this->emails->rewind();
            } else {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
                        new \obo\Services\Events\Event([
                    "onObject" => $this,
                    "name" => "afterInsert",
                    "actionAnonymousFunction" => function($arguments) {
                        if ($arguments["entity"]->valueForPropertyWithName("defaultEmail", false, false)) return;
                        $arguments["entity"]->setValueForPropertyWithName($arguments["defaultEmail"], "defaultEmail");
                        $arguments["entity"]->save();
                    },
                    "actionArguments" => ["defaultEmail" => $this->emails->rewind()],
                ]));
            }
        }
    }

    /**
     * @obo-run(afterAddToPhones)
     * @param array $event
     */
    public function controlDefaultPhone($event) {
        if (\count($this->phones) === 1) {
            if ($this->isBasedInRepository()) {
                $this->defaultPhone = $this->phones->rewind();
            } else {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
                        new \obo\Services\Events\Event([
                    "onObject" => $this,
                    "name" => "afterInsert",
                    "actionAnonymousFunction" => function($arguments) {
                        if ($arguments["entity"]->valueForPropertyWithName("defaultPhone", false, false)) return;
                        $arguments["entity"]->setValueForPropertyWithName($arguments["defaultPhone"], "defaultPhone");
                        $arguments["entity"]->save();
                    },
                    "actionArguments" => ["defaultPhone" => $this->phones->rewind()],
                ]));
            }
        }
    }

    /**
     * @obo-run(beforeDelete)
     */
    public function unsetIsDefaultAddress() {
        $this->defaultAddress = null;
    }

    /**
     * @obo-run(beforeDelete)
     */
    public function unsetIsDefaultEmail() {
        $this->defaultEmail = null;
    }

    /**
     * @obo-run(beforeDelete)
     */
    public function unsetIsDefaultPhone() {
        $this->defaultPhone = null;
    }

    /**
     * @obo-run(afterInsert,afterUpdate)
     * @param array $event
     */
    public function contorlDefaultPhoneInPhones($event) {
        if ($this->valueForPropertyWithName("defaultPhone", false, false) === null) return;
        $isDefaultPhoneInColection = false;
        foreach ($this->phones as $phone) {
            if ($phone->id === $this->valueForPropertyWithName("defaultPhone")->id) $isDefaultPhoneInColection = true;
        }
        if (!$isDefaultPhoneInColection) {
            $this->phones->add($this->valueForPropertyWithName("defaultPhone"));
        }
    }

    /**
     * @obo-run(afterInsert,afterUpdate)
     * @param array $event
     */
    public function contorlDefaultEmailInEmails($event) {
        if ($this->valueForPropertyWithName("defaultEmail", false, false) === null) return;
        $isDefaultEmailInColection = false;
        foreach ($this->emails as $email) {
            if ($email->id === $this->valueForPropertyWithName("defaultEmail")->id) $isDefaultEmailInColection = true;
        }
        if (!$isDefaultEmailInColection) {
            $this->emails->add($this->valueForPropertyWithName("defaultEmail"));
        }
    }

    /**
     * @obo-run(afterInsert,afterUpdate)
     * @param array $event
     */
    public function contorlDefaultAddressInAddresses($event) {
        if ($this->valueForPropertyWithName("defaultAddress", false, false) === null) return;
        $isDefaultAddressInColection = false;
        foreach ($this->addresses as $address) {
            if ($address->id === $this->valueForPropertyWithName("defaultAddress")->id) $isDefaultAddressInColection = true;
        }
        if (!$isDefaultAddressInColection) {
            $this->addresses->add($this->valueForPropertyWithName("defaultAddress"));
        }
    }

    /**
     * @obo-run(afterConnectToOwner)
     * @param array $event
     */
    public function conectOwner($event) {
        if ($event["owner"] instanceof AdditionalInformation || $event["owner"] instanceof Address) return;
        if ($event["owner"]->isBasedInRepository()) {
            $this->owner = $event["owner"];
        } else {
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
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
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
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

    /**
     * @obo-run(beforeSave)
     */
    public function firstBeforeSaveCallback() {
        $this->note .= "first";
    }

    /**
     * @obo-run('beforeSave')
     */
    public function secondBeforeSaveCallback() {
        $this->note .= "Second";
    }

    /**
     * @obo-run("beforeSave")
     */
    public function thirdBeforeSaveCallback() {
        $this->note .= "Third";
    }

    /**
     * @param \obo\Tests\Assets\Entities\Contacts\Address $defaultAddress
     */
    public function changeDefaultAddress(\Entities\Contacts\Address $defaultAddress) {
        $this->defaultAddress = $defaultAddress;
        $this->save();
    }

    /**
     * @param \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email $defaultEmail
     */
    public function changeDefaultEmail(AdditionalInformation\Email $defaultEmail) {
        $this->defaultEmail = $defaultEmail;
        $this->save();
    }

    /**
     * @param \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone $defaultPhone
     */
    public function changeDefaultPhone(AdditionalInformation\Phone $defaultPhone) {
        $this->defaultPhone = $defaultPhone;
        $this->save();
    }

}
