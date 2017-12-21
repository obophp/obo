<?php

namespace obo\Tests\TestCases\Carriers\EntitiesCollection;

use Tester\Assert;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @testCase
 */
class VariableForNameTest extends \Tester\TestCase {
    const DEFAULT_CONTACT_PHONE = "777 777 777";

    public function testMultipleCall() {
        $collection = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::findEntitiesAsCollection(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::querySpecification());
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage($collection));

        \Tester\Assert::same($collection->variableForName(1), \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(1));
        \Tester\Assert::same($collection->variableForName(2), \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(2));
    }

    public function createPhonesDataStorage(\obo\Carriers\EntitiesCollection $phonesCollection) {
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
            ]]);

        $dataStorageMock->shouldReceive("dataForQuery")
            ->with(\equalTo($phone2Specification))
            ->andReturn([[
                "id" => 2,
                "value" => self::DEFAULT_CONTACT_PHONE,
            ]]);

        $dataStorageMock->setDefaultDataForQueryBehavior($dataStorageMock);

        return $dataStorageMock;
    }
}

(new VariableForNameTest())->run();
