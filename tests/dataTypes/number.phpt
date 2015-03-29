<?php


/**
 * Test: checking number datatype
 */

use Tester\Assert;

require __DIR__ . "/../bootstrap.php";

\obo\Tests\DataTypes\Base\TestManager::setDataStorage(new \obo\Tests\DataTypes\Base\DataStorage());
$test = \obo\Tests\DataTypes\Base\TestManager::test([]);

test(function() use ($test) {

    $test->pnumber = 1337;
    Assert::type("int", $test->pnumber);

    $test->pnumber = 0.66;
    Assert::type("float", $test->pnumber);

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pnumber = [];
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pnumber = array(1,2,3,4,5);
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pnumber = "string";
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pnumber = new DateTime();
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pnumber = false;
    }, '\obo\Exceptions\BadDataTypeException');

});
