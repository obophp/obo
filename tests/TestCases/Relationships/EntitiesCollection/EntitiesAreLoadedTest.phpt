<?php

namespace obo\Tests\TestCases\Relationships\EntitiesCollection;

use Tester\Assert;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @testCase
 */
class EntitiesAreLoadedTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_NAME = "John Doe";

    private static $contactData = [
        "name" => self::DEFAULT_CONTACT_NAME
    ];

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function getContact() {
        return \obo\Tests\Assets\Entities\Contacts\ContactManager::entityFromArray(static::$contactData);
    }

    public function testPartialAccessToCollection() {
        $contact = $this->getContact()->save();

        isset($contact->phones[1]);
        \Tester\Assert::false($contact->phones->getEntitiesAreLoaded());

        $contact->phones->asArray([1,2]);
        \Tester\Assert::false($contact->phones->getEntitiesAreLoaded());
    }

    public function testFullAccessToCollection() {
        $contact = $this->getContact()->save();
        $contact->phones->asArray();

        \Tester\Assert::true($contact->phones->getEntitiesAreLoaded());
    }

    public function createDefaultDataStorage(\obo\Relationships\EntitiesCollection $phonesCollection) {
        return  \Mockery::mock(new \obo\Tests\Assets\DataStorage);
    }

}

(new EntitiesAreLoadedTest())->run();
