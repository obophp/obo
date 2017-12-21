<?php

namespace obo\Tests\TestCases\Carriers\EntitiesCollection;

use Tester\Assert;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @testCase
 */
class LazyLoadingTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_PHONE = "777 777 777";

    /**
     * @return \obo\Carriers\EntitiesCollection
     */
    protected function createPhonesCollection() {
        return \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::findEntitiesAsCollection(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::querySpecification());
    }

    /**
     * @return \Mockery\MockInterface
     */
    protected function configurePhoneManagerDataStorage(\obo\Carriers\EntitiesCollection$phonesCollection) {
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage($phonesCollection));

        return $phonesDataStorage;
    }

    public function testDuringInit() {
        $phonesCollection = $this->createPhonesCollection();
        $phonesDataStorage = $this->configurePhoneManagerDataStorage($phonesCollection);

        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity()));
    }

    public function testDuringCount() {
        $phonesCollection = $this->createPhonesCollection();
        $phonesDataStorage = $this->configurePhoneManagerDataStorage($phonesCollection);

        \count($phonesCollection);

        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity()));
    }

    public function testDuringSingleRead() {
        $phonesCollection = $this->createPhonesCollection();
        $phonesDataStorage = $this->configurePhoneManagerDataStorage($phonesCollection);
        $selectQuery = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::queryCarrier()->where("AND {" .\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone::entityInformation()->primaryPropertyName . "} IN (?)", [1])
            ->addSpecification($phonesCollection->getSpecification())
            ->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect());

        Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);

        $phonesCollection->variableForName(1);

        Assert::true(1 === \count($phonesDataStorage->getAllEventsForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())));
        Assert::equal($selectQuery, $phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);
    }

    public function testDuringIsset() {
        $phonesCollection = $this->createPhonesCollection();
        $phonesDataStorage = $this->configurePhoneManagerDataStorage($phonesCollection);
        $selectQuery = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::queryCarrier()->where("AND {" .\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone::entityInformation()->primaryPropertyName . "} IN (?)", [1])
            ->addSpecification($phonesCollection->getSpecification())
            ->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect());

        Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);

        isset($phonesCollection[1]);

        Assert::true(1 === \count($phonesDataStorage->getAllEventsForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())));
        Assert::equal($selectQuery, $phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);
    }

    public function testDuringMultiRead() {
        $phonesCollection = $this->createPhonesCollection();
        $phonesDataStorage = $this->configurePhoneManagerDataStorage($phonesCollection);
        $selectQuery = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::queryCarrier()
            ->addSpecification($phonesCollection->getSpecification())
            ->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect());

        Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);
        Assert::same([1 => $phonesCollection[1], 2 => $phonesCollection[2]], $phonesCollection->asArray());

        Assert::true(3 === \count($phonesDataStorage->getAllEventsForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())));
        Assert::equal($selectQuery, $phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);
    }

    public function createPhonesDataStorage($phonesCollection) {
        $phone1Specification = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::queryCarrier()->where("AND {" .\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone::entityInformation()->primaryPropertyName . "} IN (?)", [1])
            ->addSpecification($phonesCollection->getSpecification())
            ->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect());

        $phone2Specification = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::queryCarrier()->where("AND {" .\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone::entityInformation()->primaryPropertyName . "} IN (?)", [2])
            ->addSpecification($phonesCollection->getSpecification())
            ->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect());

        $phone3Specification = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::queryCarrier()
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

        $dataStorageMock->shouldReceive("dataForQuery")
            ->with(\equalTo($phone3Specification))
            ->andReturn([
                ["id" => 1, "value" => self::DEFAULT_CONTACT_PHONE,],
                ["id" => 2, "value" => self::DEFAULT_CONTACT_PHONE,],
            ]);

        $dataStorageMock->setDefaultDataForQueryBehavior($dataStorageMock);

        return $dataStorageMock;
    }
}

(new LazyLoadingTest())->run();
