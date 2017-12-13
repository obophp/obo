<?php

namespace obo\Tests;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

class SetPrimaryPropertyValueTest extends \Tester\TestCase {

    public function testValidPrimaryPropertyValueOnInitialization() {
        \Tester\Assert::exception(function() {
            $item = \obo\Tests\Assets\ItemManager::item([
                "id" => "_AB",
                "text" => "Text"
            ]);
        },"\\obo\\Exceptions\\BadValueException");

        \Tester\Assert::exception(function() {
            $item = \obo\Tests\Assets\ItemManager::item([
                "id" => "A_B",
                "text" => "Text"
            ]);
        },"\\obo\\Exceptions\\BadValueException");

        $item = \obo\Tests\Assets\ItemManager::item([
            "id" => "AB",
            "text" => "Text"
            ]);
        \Tester\Assert::same("AB", $item->primaryPropertyValue());
    }
}

(new SetPrimaryPropertyValueTest())->run();
