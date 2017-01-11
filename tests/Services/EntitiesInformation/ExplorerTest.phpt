<?php

namespace obo\Tests\Services\EntitiesInformation;

use Tester\Assert;

require __DIR__ . "/../../" . "bootstrap.php";

class ExplorerTest extends \Tester\TestCase {

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
        $path = __DIR__ . "/../../__assets/Entities";
        $explorer = new \obo\Services\EntitiesInformation\Explorer();
        \obo\DataType\CoreDataTypes::register($explorer);
        \obo\Annotation\CoreAnnotations::register($explorer);

        $foundEntitiesInformations = $explorer->analyze([$path]);
        $entityInformation = $foundEntitiesInformations[\obo\Tests\Assets\Entities\Contacts\Contact::class];

        Assert::equal(\obo\Tests\Assets\Entities\Contacts\Contact::class, $entityInformation->className);
        Assert::equal(\obo\Tests\Assets\Entities\Contacts\Contact::getReflection()->getShortName(), $entityInformation->name);

        Assert::equal(\obo\Tests\Assets\Entities\Entity::class, $entityInformation->propertiesInformation[$entityInformation->primaryPropertyName]->firstDeclaringClassName);
        Assert::equal(\obo\Tests\Assets\Entities\Entity::class, $entityInformation->propertiesInformation[$entityInformation->primaryPropertyName]->firstDeclaringClassOboName);
        Assert::false($entityInformation->propertiesInformation[$entityInformation->primaryPropertyName]->firstDeclaringClassOboNameSameAsParent);

        Assert::equal(\obo\Tests\Assets\Entities\Contacts\Contact::class, $entityInformation->propertiesInformation[$entityInformation->primaryPropertyName]->lastDeclaringClassName);
        Assert::equal(\obo\Tests\Assets\Entities\Contacts\Contact::getReflection()->getShortName(), $entityInformation->propertiesInformation[$entityInformation->primaryPropertyName]->lastDeclaringClassOboName);
    }
}

$testCase = new ExplorerTest();
$testCase->run();
