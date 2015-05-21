<?php

/**
 * Test: Invoking beforeSave event, calling order
 */

use Tester\Assert;

require __DIR__ . "/../bootstrap.php";

test(function() {
    $test = \obo\Tests\Entities\TestManager::test([]);
    $test->save();

    Assert::same("firstSecondThird", $test->testProperty);
});
