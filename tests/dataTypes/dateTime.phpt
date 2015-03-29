<?php


/**
 * Test: checking dateTime datatype
 */

use Tester\Assert;

require __DIR__ . "/../bootstrap.php";

\obo\Tests\DataTypes\Base\TestManager::setDataStorage(new \obo\Tests\DataTypes\Base\DataStorage());
$test = \obo\Tests\DataTypes\Base\TestManager::test([]);

test(function() use ($test) {

    $test->pdateTime = new DateTime();
    Assert::type("DateTime", $test->pdateTime);

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pdateTime = [];
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pdateTime = array(1,2,3,4,5);
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pdateTime = 0.66;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pdateTime = "2005-12-02 13:25:25";
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pdateTime = true;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pdateTime = 156;
    }, '\obo\Exceptions\BadDataTypeException');

});
