<?php

namespace obo\Tests\TestCases\EntitiesCollection;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

/**
 * @testCase
 */
class VariableForNameTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_ID = 1;
    const DEFAULT_CONTACT_NAME = "John Doe";
    const DEFAULT_CONTACT_PHONE = "777 777 777";
    const DEFAULT_NOTE = "Default note";

    private static $contactData = [
        "name" => self::DEFAULT_CONTACT_NAME
    ];

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function getContact() {
        return \obo\Tests\Assets\Entities\Contacts\ContactManager::entityFromArray(static::$contactData);
    }

    public function testMultipleCall() {
        $contact = $this->getContact();
        $contact->save();
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage($contact->phones));

        \Tester\Assert::same($contact->phones->variableForName(1), \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(1));
        \Tester\Assert::same($contact->phones->variableForName(2), \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(2));
    }

    public function createPhonesDataStorage(\obo\Relationships\EntitiesCollection $phonesCollection) {
        $phone1Specification = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::queryCarrier()->where("AND {" .\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone::entityInformation()->primaryPropertyName . "} IN (?)", [1])
            ->addSpecification($phonesCollection->getSpecification())
            ->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect());

        $phone2Specification = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::queryCarrier()->where("AND {" .\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone::entityInformation()->primaryPropertyName . "} IN (?)", [2])
            ->addSpecification($phonesCollection->getSpecification())
            ->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect());

        $dataStorageMock = \Mockery::mock(new \obo\Tests\Assets\DataStorage);

        $dataStorageMock->enableDataForQueryLogging($dataStorageMock);

        $dataStorageMock->shouldReceive("dataForQuery")
            ->with(\equalTo($phone1Specification))
            ->andReturn([[
                "id" => 1,
                "value" => self::DEFAULT_CONTACT_PHONE,
                "contact" => $phonesCollection->getOwner(),
            ]]);

        $dataStorageMock->shouldReceive("dataForQuery")
            ->with(\equalTo($phone2Specification))
            ->andReturn([[
                "id" => 2,
                "value" => self::DEFAULT_CONTACT_PHONE,
                "contact" => $phonesCollection->getOwner(),
            ]]);

        $dataStorageMock->setDefaultDataForQueryBehavior($dataStorageMock);

        return $dataStorageMock;
    }

}

(new VariableForNameTest())->run();
