<?php


/**
 * Test: checking boolean datatype
 */

use Tester\Assert;

require __DIR__ . "/../bootstrap.php";

\obo\Tests\DataTypes\Base\TestManager::setDataStorage(new \obo\Tests\DataTypes\Base\DataStorage());
$test = \obo\Tests\DataTypes\Base\TestManager::test([]);

test(function() use ($test) {

    $test->pboolean = true;
    Assert::type("bool", $test->pboolean);

    $test->pboolean = false;
    Assert::type("bool", $test->pboolean);

    $test->pboolean = "true";
    Assert::type("bool", $test->pboolean);

    $test->pboolean = "false";
    Assert::type("bool", $test->pboolean);

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pboolean = "string";
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pboolean = 1337;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pboolean = 0.66;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pboolean = new DateTime();
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pboolean = 1;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pboolean = 0;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pboolean = null;
    }, '\obo\Exceptions\BadDataTypeException');

});