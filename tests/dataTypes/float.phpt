<?php


/**
 * Test: checking float datatype
 */

use Tester\Assert;

require __DIR__ . "/../bootstrap.php";

\obo\Tests\DataTypes\Base\TestManager::setDataStorage(new \obo\Tests\DataTypes\Base\DataStorage());
$test = \obo\Tests\DataTypes\Base\TestManager::test([]);

test(function() use ($test) {

    $test->pfloat = 0.66;
    Assert::type("float", $test->pfloat);

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pfloat = [];
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pfloat = array(1,2,3,4,5);
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pfloat = 1337;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pfloat = new DateTime();
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pfloat = true;
    }, '\obo\Exceptions\BadDataTypeException');

});
