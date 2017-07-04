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

}
