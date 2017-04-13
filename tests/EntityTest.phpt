<?php

namespace obo\Tests;

use Tester\Assert;

require __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php";

/**
 * @testCase
 */
class EntityTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_ID = 1;
    const DEFAULT_CONTACT_NAME = "John Doe";
    const DEFAULT_ADDRESS_STREET = "West Olive Avenue";
    const DEFAULT_ADDRESS_CITY = "Burbank";
    const DEFAULT_ADDRESS_ZIP = "CA 91505-5512";
    const DEFAULT_CONTACT_PHONE = "777 777 777";
    const DEFAULT_NOTE = "Default note";

    private static $contactData = [
        "id" => self::DEFAULT_CONTACT_ID,
        "name" => self::DEFAULT_CONTACT_NAME
    ];

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function getContact() {
        return Assets\Entities\Contacts\ContactManager::entityFromArray(static::$contactData);
    }

    public function testPropertiesAsArray() {
        $contact = $this->getContact();
        Assert::equal(static::$contactData, $contact->propertiesAsArray(["id" => true, "name" => true]));
    }

    public function testEntityWithPrimaryProeprtyValue () {
        \Tester\Assert::exception(function(){Assets\Entities\Contacts\ContactManager::entityWithPrimaryPropertyValue(999);}, "\obo\Exceptions\EntityNotFoundException");
        \Tester\Assert::exception(function(){Assets\Entities\Contacts\ContactManager::entityWithPrimaryPropertyValue(0);}, "\obo\Exceptions\EntityNotFoundException");
        \Tester\Assert::same($this->getContact(), Assets\Entities\Contacts\ContactManager::entityWithPrimaryPropertyValue(static::DEFAULT_CONTACT_ID));
    }

    public function testSetProperties() {
        $data = [
            "name" => self::DEFAULT_CONTACT_NAME,
            "note" => self::DEFAULT_NOTE,
            "addresses__0_street" => self::DEFAULT_ADDRESS_STREET,
            "addresses__0_city" => self::DEFAULT_ADDRESS_CITY,
            "addresses__0_zip" => self::DEFAULT_ADDRESS_ZIP,
            "addresses__0_note_text" => self::DEFAULT_NOTE,
            "defaultPhone_value" => self::DEFAULT_CONTACT_PHONE,
            "phones__0_value" => self::DEFAULT_CONTACT_PHONE,
            "phones__1_value" => self::DEFAULT_CONTACT_PHONE,
        ];

        $contact = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact([]);
        $contact->setValuesPropertiesFromArray($data);

        \Tester\Assert::same($contact->propertiesAsArray(["name" => true, "note" => true]), ["name" => self::DEFAULT_CONTACT_NAME,"note" => self::DEFAULT_NOTE]);
        \Tester\Assert::same($contact->defaultPhone->value, self::DEFAULT_CONTACT_PHONE);
        \Tester\Assert::same($contact->phones->___0->value, self::DEFAULT_CONTACT_PHONE);
        \Tester\Assert::same($contact->phones->___1->value, self::DEFAULT_CONTACT_PHONE);
        \Tester\Assert::same($contact->addresses->___0->note->text, self::DEFAULT_NOTE);
    }

    public function testIdentityMapper() {
        $data = [
            "name" => self::DEFAULT_CONTACT_NAME,
            "note" => self::DEFAULT_NOTE,
        ];

        $e1 = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact($data);
        $e2 = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact($data);

        \Tester\Assert::true(!\obo\obo::$identityMapper->isMappedEntity($e1));
        \Tester\Assert::true(!\obo\obo::$identityMapper->isMappedEntity($e2));

        \Tester\Assert::notSame($e1, $e2);

        $data = [
            "id" => 1,
            "name" => self::DEFAULT_CONTACT_NAME,
            "note" => self::DEFAULT_NOTE,
        ];

        $e3 = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact($data);
        $e4 = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact($data);

        \Tester\Assert::true(\obo\obo::$identityMapper->isMappedEntity($e3));
        \Tester\Assert::true(\obo\obo::$identityMapper->isMappedEntity($e4));

        \Tester\Assert::same($e3, $e4);
    }

    public function testSave() {
        $contact = $this->getContact();
        Assert::false($contact->isBasedInRepository());
        $contact->save();
        Assert::true($contact->isBasedInRepository());
    }

    public function testDelete() {
        $contact = $this->getContact();
        Assert::false($contact->isDeleted());
        $contact->delete();
        Assert::true($contact->isDeleted());
    }
}

$testCase = new EntityTest();
$testCase->run();
