<?php

namespace obo\Tests\TestCases\EntitiesCollection;

require __DIR__ . "/../../bootstrap.php";

class AddItemTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_ID = 1;
    const DEFAULT_CONTACT_NAME = "John Doe";
    const DEFAULT_CONTACT_PHONE = "777 777 777";
    const DEFAULT_NOTE = "Default note";

    const DEFAULT_ADDRESS_STREET = "West Olive Avenue";
    const DEFAULT_ADDRESS_CITY = "Burbank";
    const DEFAULT_ADDRESS_ZIP = "CA 91505-5512";
    const DEFAULT_CONTACT_EMAIL = "john.doe@mail.com";

    public function testAddItem() {
        $newAddress = [
            "addresses__0_street" => self::DEFAULT_ADDRESS_STREET,
            "addresses__0_city" => self::DEFAULT_ADDRESS_CITY,
            "addresses__0_zip" => self::DEFAULT_ADDRESS_ZIP,
            "addresses__0_note_text" => self::DEFAULT_NOTE,
        ];
        $dataStorageMock = $this->createDataStorageForTestAddItem();
        \obo\Tests\Assets\Entities\Contacts\ContactManager::setDataStorage($dataStorageMock);
        \obo\Tests\Assets\Entities\Contacts\AddressManager::setDataStorage($dataStorageMock);

        $contact = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact(1);
        $contact->setValuesPropertiesFromArray($newAddress);
        \Tester\Assert::same($newAddress, $contact->propertiesAsArray([
            "addresses__0_street" => true,
            "addresses__0_city" => true,
            "addresses__0_zip" => true,
            "addresses__0_note_text" => true
            ]));

        \Tester\Assert::same(self::DEFAULT_ADDRESS_STREET, $contact->addresses["___0"]->street);
        \Tester\Assert::same(self::DEFAULT_ADDRESS_CITY, $contact->addresses["___0"]->city);
        \Tester\Assert::same(self::DEFAULT_ADDRESS_ZIP, $contact->addresses["___0"]->zip);
        \Tester\Assert::same(self::DEFAULT_NOTE, $contact->addresses["___0"]->note->text);

    }

    public function createDataStorageForTestAddItem() {
        $specification = \obo\Tests\Assets\Entities\Contacts\ContactManager::queryCarrier()
                ->select(\obo\Tests\Assets\Entities\Contacts\ContactManager::constructSelect())
                ->where("{id} = ?", 1);

        $dataStorageMock = \Mockery::mock(new \obo\Tests\Assets\DataStorage);

        $dataStorageMock->shouldReceive("dataForQuery")
                        ->with(\equalTo($specification))
                        ->andReturn([[
                            "id" => static::DEFAULT_CONTACT_ID,
                            "name" => static::DEFAULT_CONTACT_NAME,
                            "note" => static::DEFAULT_NOTE,
                        ]]);

        $newAddressSpecification = \obo\Tests\Assets\Entities\Contacts\AddressManager::queryCarrier()
                ->select(\obo\Tests\Assets\Entities\Contacts\AddressManager::constructSelect())
                ->where("AND {id} IN (?) AND {owner} = ? AND {ownerEntity} = ?", ["___0"], 1, \obo\Tests\Assets\Entities\Contacts\Contact::entityInformation()->name);

        $dataStorageMock->shouldReceive("dataForQuery")
                        ->with(\equalTo($newAddressSpecification))
                        ->andThrow(new \Exception());

        $dataStorageMock->setDefaultDataForQueryBehavior($dataStorageMock);

        return $dataStorageMock;
    }

}

(new AddItemTest())->run();
