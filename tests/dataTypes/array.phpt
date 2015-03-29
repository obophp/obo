<?php


/**
 * Test: checking array datatype
 */

use Tester\Assert;

require __DIR__ . "/../bootstrap.php";

\obo\Tests\DataTypes\Base\TestManager::setDataStorage(new \obo\Tests\DataTypes\Base\DataStorage());
$test = \obo\Tests\DataTypes\Base\TestManager::test([]);

test(function() use ($test) {

    $test->parray = ["foo" => "bar"];
    Assert::type("array", $test->parray);

    Assert::exception(function() use ($test) {
        $test->parray = "string";
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->parray = 1337;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->parray = 0.66;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->parray = new DateTime();
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->parray = true;
    }, '\obo\Exceptions\BadDataTypeException');

});