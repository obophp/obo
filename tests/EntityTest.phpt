<?php

namespace obo\Tests;

use Tester\Assert;

require __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php";

class EntityTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_ID = 1;

    private static $contactData = [
        "name" => "John Doe"
    ];

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function getContact() {
        return Assets\Entities\Contacts\ContactManager::entityFromArray(static::$contactData);
    }

    public function testPropertiesAsArray() {
        $contact = $this->getContact();
        Assert::equal(static::$contactData, $contact->propertiesAsArray(["name" => true]));
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
