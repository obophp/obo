<?php

namespace obo\Tests\TestCases\Relationships\EntitiesCollection;

use Tester\Assert;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @testCase
 */
class FindTest extends \Tester\TestCase {

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function createContact() {
        return \obo\Tests\Assets\Entities\Contacts\ContactManager::entityFromArray(["name" => "John Doe"]);
    }

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone
     */
    protected function createPhone() {
        return \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(["id" => 1, "value" => "987654321"]);
    }

    public function testFind() {
        $contact = $this->createContact();
        $phone = $this->createPhone();
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createDataStorageForTestFind($contact->phones));
        $find = $contact->phones->find(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::querySpecification()->where("AND {value} = ?", "987654321"));

        \Tester\Assert::type(\obo\Carriers\DataCarrier::class, $find);
        \Tester\Assert::equal(1, $find->count());
        \Tester\Assert::same($phone, $find->current());
    }

    public function testFindAsCollection() {
        $contact = $this->createContact();
        $phone = $this->createPhone();
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createDataStorageForTestFind($contact->phones));
        $find = $contact->phones->findAsCollection(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::querySpecification()->where("AND {value} = ?", "987654321"));

        \Tester\Assert::type(\obo\Carriers\EntitiesCollection::class, $find);
        \Tester\Assert::equal(1, \count($find->asArray()));
        \Tester\Assert::same($phone, $find->current());
    }

    public function createDataStorageForTestFind(\obo\Relationships\EntitiesCollection $phonesCollection) {
        $specification = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::queryCarrier()->where("AND {value} = ?", "987654321")
            ->addSpecification($phonesCollection->getSpecification())
            ->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect());

        $dataStorageMock = \Mockery::mock(new \obo\Tests\Assets\DataStorage());
        $dataStorageMock->shouldReceive("dataForQuery")
                ->with(\equalTo($specification))
                ->andReturn([
                    [
                        "id" => 1,
                        "value" => "987654321",
                        "owner" => $phonesCollection->getOwner(),
                    ],
                ]);
        $dataStorageMock->setDefaultDataForQueryBehavior($dataStorageMock);
        return $dataStorageMock;
    }

}

(new FindTest())->run();
