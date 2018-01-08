<?php

namespace obo\Tests\TestCases\Relationships\EntitiesCollection;

use Tester\Assert;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @testCase
 */
class EntitiesCollectionTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_ID = 1;
    const DEFAULT_CONTACT_NAME = "John Doe";
    const DEFAULT_CONTACT_PHONE = "777 777 777";
    const DEFAULT_NOTE = "Default note";

    private static $contactData = [
        "name" => "John Doe"
    ];

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function getContact() {
        return \obo\Tests\Assets\Entities\Contacts\ContactManager::entityFromArray(static::$contactData);
    }

    public function testCount() {
        $contact = $this->getContact();
        \Tester\Assert::same($contact->phones->count(), 0);

        $contact->phones->add(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(["value" => static::DEFAULT_CONTACT_PHONE]));
        \Tester\Assert::same($contact->phones->count(), 1);

        $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);

        \Tester\Assert::same($contact->phones->count(), 2);

        $contact->save();

        $contact->phones->add(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(["value" => static::DEFAULT_CONTACT_PHONE]));
        $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);

        \Tester\Assert::same($contact->phones->count(), 4);
    }

    public function testLoadEntitiesWithOverwriteQueryCarrier() {
        $exceptedArray = [
            1 => [
                "id" => 1,
                "name" => "Business subject one",
                "original" => null,
                "deleted" => false,
                "createdAt" => null,
                "updatedAt" => null,
            ],
            5 => [
                "id" => 5,
                "name" => "Business subject two",
                "original" => null,
                "deleted" => false,
                "createdAt" => null,
                "updatedAt" => null,
            ],
        ];

        $dataStorage = $this->getDataStorageForLoadEntitiesWithOverwriteQueryCarrier();
        \obo\Tests\Assets\Entities\BusinessSubjectManager::setDataStorage($dataStorage);
        $collection = \obo\Tests\Assets\Entities\BusinessSubjectManager::findEntitiesAsCollection(\obo\Tests\Assets\Entities\BusinessSubjectManager::querySpecification());

        $array = [];
        foreach ($collection as $id => $entity) $array[$id] = $entity->propertiesAsArray();

        Assert::same($exceptedArray, $array);
    }

    public function getDataStorageForLoadEntitiesWithOverwriteQueryCarrier() {
        $mockStorage = \Mockery::mock(new \obo\Tests\Assets\DataStorage);

        $corectSpecification = \obo\Carriers\QueryCarrier::instance()->select(\obo\Tests\Assets\Entities\BusinessSubjectManager::constructSelect())->where("AND {original} IS NULL")->where("AND {deleted} = ?", 0);
        $corectSpecification->setDefaultEntityClassName(\obo\Tests\Assets\Entities\BusinessSubject::class);

        $mockStorage->shouldReceive("dataForQuery")
            ->with(\equalTo($corectSpecification))
            ->andReturn(
                [
                    [
                        "id" => 1,
                        "name" => "Business subject one",
                        "original" => null,
                        "deleted" => false,
                    ],
                    [
                        "id" => 5,
                        "name" => "Business subject two",
                        "original" => null,
                        "deleted" => false,
                    ]
                ]
                );
        $mockStorage->setDefaultDataForQueryBehavior($mockStorage);
        return $mockStorage;
    }

    public function testAdd() {
        $contact = $this->getContact();

        $phones = [
            "__0" => \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(["value" => static::DEFAULT_CONTACT_PHONE]),
            "__1" => \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(["value" => static::DEFAULT_CONTACT_PHONE]),
            "__2" => \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(["value" => static::DEFAULT_CONTACT_PHONE]),
        ];

        $contact->phones->add($phones);

        \Tester\Assert::same(3, $contact->phones->count());
        \Tester\Assert::same($phones, $contact->phones->asArray());
    }

    public function testAddNew() {
        \obo\Tests\Assets\Entities\Contacts\ContactManager::setDataStorage($this->createDataStorageForTestAddNew());
        $contact = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact(1);

        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone::on("beforeInsert", function($event) use ($contact) {
            \Tester\Assert::same($contact, $event["entity"]->contact);
        });

        $phone = $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);
        \Tester\Assert::same($phone, $contact->phones->{$phone->primaryPropertyValue()});
    }

    public function testAddAndAddNewAfterSave() {
        $contact = $this->getContact();

        $phones = [
            $phone1 = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(["value" => static::DEFAULT_CONTACT_PHONE]),
            $phone2 = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(["value" => static::DEFAULT_CONTACT_PHONE]),
        ];

        $contact->phones->add($phones);

        $phone3 = $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);
        $phone4 = $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);

        $contact->save();

        \Tester\Assert::same(["1" => $phone3, "2" => $phone4, "4" => $phone1, "5" => $phone2], $contact->phones->asArray());
    }

    public function createDataStorageForTestAddNew() {
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

        $dataStorageMock->setDefaultDataForQueryBehavior($dataStorageMock);

        return $dataStorageMock;
    }

}

(new EntitiesCollectionTest())->run();
