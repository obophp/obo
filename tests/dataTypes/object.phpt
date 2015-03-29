<?php


/**
 * Test: checking object datatype
 */

use Tester\Assert;

require __DIR__ . "/../bootstrap.php";

class TestObject {

}

\obo\Tests\DataTypes\Base\TestManager::setDataStorage(new \obo\Tests\DataTypes\Base\DataStorage());
$test = \obo\Tests\DataTypes\Base\TestManager::test([]);

test(function() use ($test) {

    $test->pobject = new DateTime();
    Assert::type("object", $test->pobject);

    $test->pobject = new TestObject();
    Assert::type("object", $test->pobject);

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pobject = [];
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pobject = array(1,2,3,4,5);
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pobject = 0.66;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pobject = 1337;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pobject = false;
    }, '\obo\Exceptions\BadDataTypeException');

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pobject = "string";
    }, '\obo\Exceptions\BadDataTypeException');

});
