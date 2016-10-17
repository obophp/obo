<?php

namespace obo\Tests\Services\EntitiesInformation;

use Tester\Assert;

require __DIR__ . "/../../" . "bootstrap.php";

class ExplorerTest extends \Tester\TestCase {

    public function testFindClasses() {
        $dirPath = __DIR__ . "/../../__assets/Entities/Entity";
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath), \RecursiveDirectoryIterator::CURRENT_AS_FILEINFO);
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
}

$testCase = new ExplorerTest();
$testCase->run();
