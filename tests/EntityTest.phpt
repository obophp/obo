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

    private static $simpleContactData = [
        "id" => self::DEFAULT_CONTACT_ID,
        "name" => self::DEFAULT_CONTACT_NAME
    ];

    private static $extendedContactData = [
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

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function getSimpleContact() {
        return Assets\Entities\Contacts\ContactManager::entityFromArray(static::$simpleContactData);
    }

    protected function getExtendedContact() {
        return Assets\Entities\Contacts\ContactManager::entityFromArray(static::$extendedContactData);
    }

    public function testPropertiesAsArray() {
        $contact = $this->getSimpleContact();
        Assert::equal(static::$simpleContactData, $contact->propertiesAsArray(["id" => true, "name" => true]));
    }

    public function testEntityWithPrimaryPropertyValue () {
        \Tester\Assert::exception(function(){Assets\Entities\Contacts\ContactManager::entityWithPrimaryPropertyValue(999);}, "\obo\Exceptions\EntityNotFoundException");
        \Tester\Assert::exception(function(){Assets\Entities\Contacts\ContactManager::entityWithPrimaryPropertyValue(0);}, "\obo\Exceptions\EntityNotFoundException");
        \Tester\Assert::same($this->getSimpleContact(), Assets\Entities\Contacts\ContactManager::entityWithPrimaryPropertyValue(static::DEFAULT_CONTACT_ID));
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
        $contact->setValuesPropertiesFromArray(static::$extendedContactData);

        \Tester\Assert::same(["name" => self::DEFAULT_CONTACT_NAME, "note" => self::DEFAULT_NOTE], $contact->propertiesAsArray(["name" => true, "note" => true]) );
        \Tester\Assert::same(self::DEFAULT_CONTACT_PHONE, $contact->defaultPhone->value);
        \Tester\Assert::same(self::DEFAULT_CONTACT_PHONE, $contact->phones->___0->value);
        \Tester\Assert::same(self::DEFAULT_CONTACT_PHONE, $contact->phones->___1->value);
        \Tester\Assert::same(self::DEFAULT_NOTE, $contact->addresses->___0->note->text);
    }

    public function testChangePropertiesOfStoredEntity() {
        $dataStorageMock = $this->createDataStorageMockForChangePropertiesOfStoredEntity();
        \obo\Tests\Assets\Entities\Contacts\ContactManager::setDataStorage($dataStorageMock);
        \obo\Tests\Assets\Entities\Contacts\AddressManager::setDataStorage($dataStorageMock);

        $contact = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact(1);

        $data = ["name" => "new_" . \obo\Tests\EntityTest::DEFAULT_CONTACT_NAME, "addresses_1_street" => "new_" . self::DEFAULT_ADDRESS_STREET,];
        $contact->changeValuesPropertiesFromArray($data);

        \Tester\Assert::same($data, $contact->propertiesAsArray(["name" => true, "addresses_1_street" => true]));
    }

    public function createDataStorageMockForChangePropertiesOfStoredEntity() {
        $contactSpecification = Assets\Entities\Contacts\ContactManager::queryCarrier()
                ->select(\obo\Tests\Assets\Entities\Contacts\ContactManager::constructSelect())
                ->where("{id} = ?", 1);

        $dataStorageMock = \Mockery::mock(new \obo\Tests\Assets\DataStorage);

        $dataStorageMock->shouldReceive("dataForQuery")
                        ->with(\equalTo($contactSpecification))
                        ->andReturn([[
                            "id" => 1,
                            "name" => \obo\Tests\EntityTest::DEFAULT_CONTACT_NAME,
                            "note" => \obo\Tests\EntityTest::DEFAULT_NOTE,
                        ]]);

        $addressSpecification = \obo\Tests\Assets\Entities\Contacts\AddressManager::queryCarrier()
                ->select(\obo\Tests\Assets\Entities\Contacts\AddressManager::constructSelect())
                ->where("AND {id} IN (?) AND {owner} = ? AND {ownerEntity} = ?", ["1"], 1, \obo\Tests\Assets\Entities\Contacts\Contact::entityInformation()->name);

        $dataStorageMock->shouldReceive("dataForQuery")
                        ->with(\equalTo($addressSpecification))
                        ->andReturn([[
                            "id" => 1,
                            "street" => self::DEFAULT_ADDRESS_STREET,
                            "city" => self::DEFAULT_ADDRESS_CITY,
                            "zip" => self::DEFAULT_ADDRESS_ZIP,
                        ]]);

        $dataStorageMock->setDefaultDataForQueryBehavior($dataStorageMock);

        return $dataStorageMock;
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
        $contact = $this->getSimpleContact();
        Assert::false($contact->isBasedInRepository());
        $contact->save();
        Assert::true($contact->isBasedInRepository());
    }

    public function testDelete() {
        $contact = $this->getSimpleContact();
        Assert::false($contact->isDeleted());
        $contact->delete();
        Assert::true($contact->isDeleted());
    }

    public function testMetaData() {
        $metaData = 'a:2:{s:6:"static";a:8:{s:4:"name";s:7:"Contact";s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:15:"parentClassName";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:11:"storageName";s:0:"";s:14:"repositoryName";s:28:"TestsEntitiesContactsContact";s:19:"primaryPropertyName";s:2:"id";s:10:"properties";a:11:{s:2:"id";a:5:{s:8:"dataType";s:7:"integer";s:11:"persistable";b:1;s:10:"columnName";s:2:"id";s:18:"ownerEntityHistory";a:2:{s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:7:"Contact";}s:17:"declaredInClasses";a:3:{i:0;a:3:{s:9:"className";s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:4:"name";s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:11:"annotations";a:3:{s:12:"obo-dataType";a:1:{i:0;s:7:"integer";}s:17:"obo-autoIncrement";a:1:{i:0;b:1;}s:14:"obo-columnName";a:1:{i:0;s:2:"id";}}}i:1;a:3:{s:9:"className";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:2:{s:12:"obo-dataType";a:1:{i:0;s:7:"integer";}s:17:"obo-autoIncrement";a:1:{i:0;b:1;}}}i:2;a:3:{s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:2:{s:12:"obo-dataType";a:1:{i:0;s:7:"integer";}s:17:"obo-autoIncrement";a:1:{i:0;b:1;}}}}}s:6:"phones";a:5:{s:8:"dataType";s:6:"object";s:11:"persistable";b:0;s:12:"relationship";a:4:{s:4:"type";s:4:"many";s:28:"entityClassNameToBeConnected";s:62:"obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone";s:7:"cascade";s:12:"save, delete";s:18:"connectViaProperty";s:7:"contact";}s:18:"ownerEntityHistory";a:1:{s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:7:"Contact";}s:17:"declaredInClasses";a:2:{i:0;a:3:{s:9:"className";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:8:"obo-many";a:3:{s:12:"targetEntity";s:71:"\obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Phone";s:18:"connectViaProperty";s:7:"contact";s:7:"cascade";s:11:"save,delete";}}}i:1;a:3:{s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:8:"obo-many";a:3:{s:12:"targetEntity";s:63:"\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone";s:18:"connectViaProperty";s:7:"contact";s:7:"cascade";s:11:"save,delete";}}}}}s:6:"emails";a:5:{s:8:"dataType";s:6:"object";s:11:"persistable";b:0;s:12:"relationship";a:4:{s:4:"type";s:4:"many";s:28:"entityClassNameToBeConnected";s:62:"obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email";s:7:"cascade";s:12:"save, delete";s:18:"connectViaProperty";s:7:"contact";}s:18:"ownerEntityHistory";a:1:{s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:7:"Contact";}s:17:"declaredInClasses";a:2:{i:0;a:3:{s:9:"className";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:8:"obo-many";a:3:{s:12:"targetEntity";s:71:"\obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Email";s:18:"connectViaProperty";s:7:"contact";s:7:"cascade";s:11:"save,delete";}}}i:1;a:3:{s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:8:"obo-many";a:3:{s:12:"targetEntity";s:63:"\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email";s:18:"connectViaProperty";s:7:"contact";s:7:"cascade";s:11:"save,delete";}}}}}s:9:"addresses";a:5:{s:8:"dataType";s:6:"object";s:11:"persistable";b:0;s:12:"relationship";a:5:{s:4:"type";s:4:"many";s:28:"entityClassNameToBeConnected";s:42:"obo\Tests\Assets\Entities\Contacts\Address";s:7:"cascade";s:0:"";s:18:"connectViaProperty";s:5:"owner";s:19:"ownerNameInProperty";s:11:"ownerEntity";}s:18:"ownerEntityHistory";a:1:{s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:7:"Contact";}s:17:"declaredInClasses";a:2:{i:0;a:3:{s:9:"className";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:8:"obo-many";a:3:{s:12:"targetEntity";s:51:"\obo\Tests\Assets\AbstractEntities\Contacts\Address";s:18:"connectViaProperty";s:5:"owner";s:19:"ownerNameInProperty";s:11:"ownerEntity";}}}i:1;a:3:{s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:8:"obo-many";a:3:{s:12:"targetEntity";s:43:"\obo\Tests\Assets\Entities\Contacts\Address";s:18:"connectViaProperty";s:5:"owner";s:19:"ownerNameInProperty";s:11:"ownerEntity";}}}}}s:14:"defaultAddress";a:6:{s:8:"dataType";s:6:"entity";s:11:"persistable";b:1;s:12:"relationship";a:4:{s:4:"type";s:3:"one";s:28:"entityClassNameToBeConnected";s:42:"obo\Tests\Assets\Entities\Contacts\Address";s:7:"cascade";s:12:"save, delete";s:10:"autocreate";b:1;}s:10:"columnName";s:14:"defaultAddress";s:18:"ownerEntityHistory";a:1:{s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:7:"Contact";}s:17:"declaredInClasses";a:2:{i:0;a:3:{s:9:"className";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:7:"obo-one";a:4:{s:12:"targetEntity";s:51:"\obo\Tests\Assets\AbstractEntities\Contacts\Address";s:10:"autoCreate";b:1;s:7:"cascade";s:4:"save";s:5:"eager";s:4:"true";}}}i:1;a:3:{s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:2:{s:7:"obo-one";a:3:{s:12:"targetEntity";s:43:"\obo\Tests\Assets\Entities\Contacts\Address";s:10:"autoCreate";b:1;s:7:"cascade";s:12:"save, delete";}s:18:"obo-repositoryName";a:1:{i:0;s:28:"TestsEntitiesContactsContact";}}}}}s:12:"defaultPhone";a:6:{s:8:"dataType";s:6:"entity";s:11:"persistable";b:1;s:12:"relationship";a:4:{s:4:"type";s:3:"one";s:28:"entityClassNameToBeConnected";s:62:"obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone";s:7:"cascade";s:4:"save";s:10:"autocreate";b:1;}s:10:"columnName";s:12:"defaultPhone";s:18:"ownerEntityHistory";a:1:{s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:7:"Contact";}s:17:"declaredInClasses";a:2:{i:0;a:3:{s:9:"className";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:7:"obo-one";a:3:{s:12:"targetEntity";s:71:"\obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Phone";s:10:"autoCreate";b:1;s:7:"cascade";s:4:"save";}}}i:1;a:3:{s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:7:"obo-one";a:3:{s:12:"targetEntity";s:63:"\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone";s:10:"autoCreate";b:1;s:7:"cascade";s:4:"save";}}}}}s:12:"defaultEmail";a:6:{s:8:"dataType";s:6:"entity";s:11:"persistable";b:1;s:12:"relationship";a:4:{s:4:"type";s:3:"one";s:28:"entityClassNameToBeConnected";s:62:"obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email";s:7:"cascade";s:4:"save";s:10:"autocreate";b:1;}s:10:"columnName";s:12:"defaultEmail";s:18:"ownerEntityHistory";a:1:{s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:7:"Contact";}s:17:"declaredInClasses";a:2:{i:0;a:3:{s:9:"className";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:7:"obo-one";a:3:{s:12:"targetEntity";s:71:"\obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Email";s:10:"autoCreate";b:1;s:7:"cascade";s:4:"save";}}}i:1;a:3:{s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:7:"obo-one";a:3:{s:12:"targetEntity";s:63:"\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email";s:10:"autoCreate";b:1;s:7:"cascade";s:4:"save";}}}}}s:4:"name";a:5:{s:8:"dataType";s:6:"string";s:11:"persistable";b:1;s:10:"columnName";s:4:"name";s:18:"ownerEntityHistory";a:1:{s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:7:"Contact";}s:17:"declaredInClasses";a:2:{i:0;a:3:{s:9:"className";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:12:"obo-dataType";a:1:{i:0;s:6:"string";}}}i:1;a:3:{s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:12:"obo-dataType";a:1:{i:0;s:6:"string";}}}}}s:4:"note";a:5:{s:8:"dataType";s:6:"string";s:11:"persistable";b:1;s:10:"columnName";s:4:"note";s:18:"ownerEntityHistory";a:1:{s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:7:"Contact";}s:17:"declaredInClasses";a:2:{i:0;a:3:{s:9:"className";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:12:"obo-dataType";a:1:{i:0;s:6:"string";}}}i:1;a:3:{s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:12:"obo-dataType";a:1:{i:0;s:6:"string";}}}}}s:9:"createdAt";a:5:{s:8:"dataType";s:8:"dateTime";s:11:"persistable";b:1;s:10:"columnName";s:9:"createdAt";s:18:"ownerEntityHistory";a:2:{s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:7:"Contact";}s:17:"declaredInClasses";a:3:{i:0;a:3:{s:9:"className";s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:4:"name";s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:11:"annotations";a:1:{s:13:"obo-timeStamp";a:1:{i:0;s:12:"beforeInsert";}}}i:1;a:3:{s:9:"className";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:13:"obo-timeStamp";a:1:{i:0;s:12:"beforeInsert";}}}i:2;a:3:{s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:13:"obo-timeStamp";a:1:{i:0;s:12:"beforeInsert";}}}}}s:9:"updatedAt";a:5:{s:8:"dataType";s:8:"dateTime";s:11:"persistable";b:1;s:10:"columnName";s:9:"updatedAt";s:18:"ownerEntityHistory";a:2:{s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:7:"Contact";}s:17:"declaredInClasses";a:3:{i:0;a:3:{s:9:"className";s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:4:"name";s:40:"obo\Tests\Assets\AbstractEntities\Entity";s:11:"annotations";a:1:{s:13:"obo-timeStamp";a:1:{i:0;s:12:"beforeUpdate";}}}i:1;a:3:{s:9:"className";s:50:"obo\Tests\Assets\AbstractEntities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:13:"obo-timeStamp";a:1:{i:0;s:12:"beforeUpdate";}}}i:2;a:3:{s:9:"className";s:42:"obo\Tests\Assets\Entities\Contacts\Contact";s:4:"name";s:7:"Contact";s:11:"annotations";a:1:{s:13:"obo-timeStamp";a:1:{i:0;s:12:"beforeUpdate";}}}}}}s:16:"eagerConnections";a:1:{i:0;s:14:"defaultAddress";}}s:7:"dynamic";a:5:{s:23:"objectIdentificationKey";s:32:"00000000000000000000000000000000";s:23:"entityIdentificationKey";N;s:20:"primaryPropertyValue";s:0:"";s:11:"initialized";b:1;s:17:"basedInRepository";b:0;}}';
        \Tester\Assert::same($metaData, \preg_replace("#\"objectIdentificationKey\"\;s\:32\:\"([a-z0-9]{32})\"#", "\"objectIdentificationKey\";s:32:\"00000000000000000000000000000000\"", \serialize($this->getExtendedContact()->metaData())));
    }

}

$testCase = new EntityTest();
$testCase->run();
