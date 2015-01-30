<?php


/**
 * Test: Invoking beforeSave event, calling order
 */

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

test(function() {
    \obo\Tests\Events\BeforeSave\TestManager::setDataStorage(new \obo\Tests\Events\BeforeSave\DataStorage());
    $test = \obo\Tests\Events\BeforeSave\TestManager::test([]);
    $test->save();

    Assert::same("firstSecondThird", $test->testProperty);
});
