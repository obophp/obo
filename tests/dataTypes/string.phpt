<?php


/**
 * Test: checking string datatype
 */

use Tester\Assert;

require __DIR__ . "/../bootstrap.php";

\obo\Tests\DataTypes\Base\TestManager::setDataStorage(new \obo\Tests\DataTypes\Base\DataStorage());
$test = \obo\Tests\DataTypes\Base\TestManager::test([]);

test(function() use ($test) {

    $test->pstring = "test";
    Assert::type("string", $test->pstring);

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pstring = array(1,2,3,4,5);
    }, '\obo\Exceptions\BadDataTypeException');

    $test->pstring = 1337;
    Assert::type("scalar", $test->pstring);

    $test->pstring = 0.66;
    Assert::type("scalar", $test->pstring);

    Assert::exception(function() use ($test) {
        \obo\obo::run();
        $test->pstring = new DateTime();
    }, '\obo\Exceptions\BadDataTypeException');

    $test->pstring = true;
    Assert::type("scalar", $test->pstring);

});