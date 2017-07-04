<?php

namespace obo\Tests\Assets\AbstractEntities\Contacts;

/**
 * @obo-name(Contact)
 * @obo-repositoryName(TestsEntitiesContactsContact)
 * @property string $name
 * @property \obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Phone[] $phones
 * @property \obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Email[] $emails
 * @property \obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Url[] $urls
 * @property \obo\Tests\Assets\AbstractEntities\Contacts\Address[] $addresses
 * @property \obo\Tests\Assets\AbstractEntities\Contacts\Address $defaultAddress
 * @property \obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Phone $defaultPhone
 * @property \obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Email $defaultEmail
 * @property string note
 */
abstract class Contact extends \obo\Tests\Assets\AbstractEntities\Entity {

    /**
     * @param \obo\Tests\Assets\AbstractEntities\Contacts\Address $defaultAddress
     */
    public function changeDefaultAddress(\AbstractEntities\Contacts\Address $defaultAddress) {
        $this->defaultAddress = $defaultAddress;
        $this->save();
    }

    /**
     * @param \obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Email $defaultEmail
     */
    public function changeDefaultEmail(AdditionalInformation\Email $defaultEmail) {
        $this->defaultEmail = $defaultEmail;
        $this->save();
    }

    /**
     * @param \obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Phone $defaultPhone
     */
    public function changeDefaultPhone(AdditionalInformation\Phone $defaultPhone) {
        $this->defaultPhone = $defaultPhone;
        $this->save();
    }

}
