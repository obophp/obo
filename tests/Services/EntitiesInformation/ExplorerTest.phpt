<?php

namespace obo\Tests\Services\EntitiesInformation;

use Tester\Assert;

require __DIR__ . "/../../" . "bootstrap.php";

class ExplorerTest extends \Tester\TestCase {

    protected static $annotationsForEntityPrimaryProperty = [
        "obo-dataType" => ["integer"],
        "obo-autoIncrement" => [true],
        "obo-columnName" => ["id"]
    ];

    protected static $declaredInClassesForContactDefaultAddressProperty = [
        [
            "className" => "obo\\Tests\\Assets\\AbstractEntities\\Contacts\\Contact",
            "name" => "Contact",
            "annotations" => [
            "obo-one" =>
                [
                    "targetEntity" => "\obo\\Tests\\Assets\\AbstractEntities\\Contacts\\Address",
                    "autoCreate" => true,
                    "cascade" => "save",
                    "eager" => "true",
                ],
            ],
        ],
        [
            "className" => "obo\\Tests\\Assets\\Entities\\Contacts\\Contact",
            "name" => "Contact",
            "annotations" => [
            "obo-one" =>
                [
                    "targetEntity" => "\obo\\Tests\\Assets\\Entities\\Contacts\\Address",
                    "autoCreate" => true,
                    "cascade" => "save, delete",
                ],
            "obo-repositoryName" =>
                [
                    "TestsEntitiesContactsContact"
                ],
            ],
        ],
    ];

    public function testFindClasses() {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . "/../../__assets/Entities/Entity"), \RecursiveDirectoryIterator::CURRENT_AS_FILEINFO);
        $files = new \RegexIterator($iterator, '#^.+\.php$#', \RegexIterator::MATCH, \RegexIterator::USE_KEY);
        $content = "";

        foreach ($files as $fileName) {
            $content .= file_get_contents($fileName);
        }

        $expectedClasses = [
            'obo\Tests\Assets\Entities\Entity',
            'obo\Tests\Assets\Entities\EntityManager',
            'obo\Tests\Assets\Entities\EntityProperties',
        ];

        $foundClasses = \obo\Services\EntitiesInformation\Explorer::findClasses($content);
        $difference = array_diff($expectedClasses, $foundClasses);
        Assert::true(empty($difference), "Found class list does not match the expected one");
    }

    public function testAnalyze() {
        $path = __DIR__ . "/../../__assets";
        $explorer = new \obo\Services\EntitiesInformation\Explorer();
        \obo\DataType\CoreDataTypes::register($explorer);
        \obo\Annotation\CoreAnnotations::register($explorer);

        $foundEntitiesInformation = $explorer->analyze([$path]);
        $entityInformation = $foundEntitiesInformation[\obo\Tests\Assets\Entities\Contacts\Contact::class];

        Assert::equal(\obo\Tests\Assets\Entities\Contacts\Contact::class, $entityInformation->className);
        Assert::equal(\obo\Tests\Assets\Entities\Contacts\Contact::getReflection()->getShortName(), $entityInformation->name);

        $ownerEntityHistory = $entityInformation->propertiesInformation[$entityInformation->primaryPropertyName]->ownerEntityHistory;

        Assert::equal(\obo\Tests\Assets\AbstractEntities\Entity::class, $ownerEntityHistory[\obo\Tests\Assets\AbstractEntities\Entity::class]);
        Assert::equal(\obo\Tests\Assets\Entities\Contacts\Contact::getReflection()->getShortName(), $ownerEntityHistory[\obo\Tests\Assets\AbstractEntities\Contacts\Contact::class]);
        Assert::false(array_key_exists(\obo\Tests\Assets\Entities\Entity::class, $ownerEntityHistory), "Entity with name " . \obo\Tests\Assets\Entities\Entity::class . " should not be in the ownerHistory list, because entity name matches parent entity name.");
        Assert::equal(\obo\Tests\Assets\AbstractEntities\Entity::class, $ownerEntityHistory[\obo\Tests\Assets\AbstractEntities\Entity::class]);

        $primaryPropertyDeclaredInClasses = $entityInformation->propertiesInformation[$entityInformation->primaryPropertyName]->declaredInClasses;
        $baseEntityDeclaredInClasses = current($primaryPropertyDeclaredInClasses);
        $contactEntityDeclaredInClasses = end($primaryPropertyDeclaredInClasses);

        Assert::equal(\obo\Tests\Assets\AbstractEntities\Entity::class, $baseEntityDeclaredInClasses["className"]);
        Assert::equal(\obo\Tests\Assets\AbstractEntities\Entity::class, $baseEntityDeclaredInClasses["name"]);
        Assert::equal(\obo\Tests\Assets\Entities\Contacts\Contact::class, $contactEntityDeclaredInClasses["className"]);
        Assert::equal(\obo\Tests\Assets\Entities\Contacts\Contact::getReflection()->getShortName(), $contactEntityDeclaredInClasses["name"]);

        Assert::same(self::$annotationsForEntityPrimaryProperty, $baseEntityDeclaredInClasses["annotations"]);
        Assert::true(array_key_exists("obo-columnName", $baseEntityDeclaredInClasses["annotations"]));
        Assert::false(array_key_exists("obo-columnName", $contactEntityDeclaredInClasses["annotations"]));

        Assert::equal(self::$declaredInClassesForContactDefaultAddressProperty, $entityInformation->propertiesInformation["defaultAddress"]->declaredInClasses);
    }

    public function testGettersAndSettersForEntity() {
        $path = __DIR__ . "/../../__assets";
        $explorer = new \obo\Services\EntitiesInformation\Explorer();
        \obo\DataType\CoreDataTypes::register($explorer);
        \obo\Annotation\CoreAnnotations::register($explorer);

        $foundEntitiesInformation = $explorer->analyze([$path]);
        $entityInformation = $foundEntitiesInformation[\obo\Tests\Assets\Entities\Entity\StaticAnalysis\Dummy::class];

        Assert::equal("get_property", $entityInformation->propertiesInformation["_property"]->getterName);
        Assert::equal("set_property", $entityInformation->propertiesInformation["_property"]->setterName);

        Assert::equal("get__property", $entityInformation->propertiesInformation["__property"]->getterName);
        Assert::equal("set__property", $entityInformation->propertiesInformation["__property"]->setterName);

        Assert::equal("get___property", $entityInformation->propertiesInformation["___property"]->getterName);
        Assert::equal("set___property", $entityInformation->propertiesInformation["___property"]->setterName);

        Assert::equal("getProperty", $entityInformation->propertiesInformation["property"]->getterName);
        Assert::equal("setProperty", $entityInformation->propertiesInformation["property"]->setterName);

        Assert::false(isset($entityInformation->propertiesInformation["8property"]));

        Assert::equal("get__5Property", $entityInformation->propertiesInformation["__5Property"]->getterName);
        Assert::equal("set__5Property", $entityInformation->propertiesInformation["__5Property"]->setterName);

        Assert::equal("get_4_property", $entityInformation->propertiesInformation["_4_property"]->getterName);
        Assert::equal("set_4_property", $entityInformation->propertiesInformation["_4_property"]->setterName);

        Assert::equal("getA5Property", $entityInformation->propertiesInformation["a5Property"]->getterName);
        Assert::equal("setA5Property", $entityInformation->propertiesInformation["a5Property"]->setterName);
    }
}

$testCase = new ExplorerTest();
$testCase->run();
