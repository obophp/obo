<?php

namespace obo\Tests\TestCases\Annotation\Base;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @testCase
 */
class DefinitionWithNumberOfParametersTest extends \Tester\TestCase {

    protected function prepareDefinition($name, $value, $scope = "entity") {
        \obo\Tests\TestCases\Annotation\Base\Assets\Definitions\DefinitionWithParametersNumber::$name = $name;
        \obo\Tests\TestCases\Annotation\Base\Assets\Definitions\DefinitionWithParametersNumber::$scope = $scope;
        \obo\Tests\TestCases\Annotation\Base\Assets\Definitions\DefinitionWithParametersNumber::$value = $value;
        return new \obo\Tests\TestCases\Annotation\Base\Assets\Definitions\DefinitionWithParametersNumber(new \obo\Carriers\EntityInformationCarrier());
    }

    public function testWrongParameters() {
        \Tester\Assert::exception(function () {
            $this->prepareDefinition("wrong", "veryBadValue")->process(["oneParam" => "oneValue"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'wrong' expects number or one of predefined constants, 'veryBadValue' given");
    }

    public function testZeroParameters() {
        \Tester\Assert::null($this->prepareDefinition("zero", \obo\Annotation\Base\Definition::ZERO_PARAMETERS)->process([]));
    }

    public function testZeroParametersWrong() {
        \Tester\Assert::exception(function () {
            $this->prepareDefinition("zero", \obo\Annotation\Base\Definition::ZERO_PARAMETERS)->process(["oneParam" => "oneValue"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'zero' expects 0 parameters, 1 parameters given");
    }

    public function testZeroOrOneParameter() {
        $definition = $this->prepareDefinition("zeroOrOne", \obo\Annotation\Base\Definition::ZERO_OR_ONE_PARAMETER);

        \Tester\Assert::null($definition->process([]));
        \Tester\Assert::null($definition->process(["oneParam" => "oneValue"]));
    }

    public function testZeroOrOneParameterWrong() {
        \Tester\Assert::exception(function () {
            $this->prepareDefinition("zeroOrOne", \obo\Annotation\Base\Definition::ZERO_OR_ONE_PARAMETER)->process(["firstParam" => "firstValue", "secondParam" => "secondValue"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'zeroOrOne' expects zero or one parameter, more parameters given");
    }

    public function testOneOrMoreParameter() {
        $definition = $this->prepareDefinition("oneOrMore", \obo\Annotation\Base\Definition::ONE_OR_MORE_PARAMETERS);

        \Tester\Assert::null($definition->process(["oneParam" => "oneValue"]));
        \Tester\Assert::null($definition->process(["firstParam" => "firstValue", "secondParam" => "secondValue"]));
    }

    public function testOneOrMoreParameterWrong() {
        \Tester\Assert::exception(function () {
            $this->prepareDefinition("oneOrMore", \obo\Annotation\Base\Definition::ONE_OR_MORE_PARAMETERS)->process([]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'oneOrMore' expects one or more parameters, no parameters given");
    }

    public function testNumberParameter() {
        \Tester\Assert::null($this->prepareDefinition("numberZero", 0)->process([]));
        \Tester\Assert::null($this->prepareDefinition("numberOne", 1)->process(["one" => "one"]));
        \Tester\Assert::null($this->prepareDefinition("numberThree", 3)->process(["one" => "one", "two" => "two", "three" => "three"]));
    }

    public function testNumberParameterWrong() {
        \Tester\Assert::exception(function () {
            $this->prepareDefinition("numberZero", 0)->process(["one" => "one"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'numberZero' expects 0 parameters, 1 parameters given");

        $definition = $this->prepareDefinition("numberOne", 1);

        \Tester\Assert::exception(function () use ($definition) {
            $definition->process([]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'numberOne' expects 1 parameters, 0 parameters given");
        \Tester\Assert::exception(function () use ($definition) {
            $definition->process(["one" => "one", "two" => "two"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'numberOne' expects 1 parameters, 2 parameters given");

        $definition = $this->prepareDefinition("numberThree", 3);

        \Tester\Assert::exception(function () use ($definition) {
            $definition->process([]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'numberThree' expects 3 parameters, 0 parameters given");
        \Tester\Assert::exception(function () use ($definition) {
            $definition->process(["one" => "one"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'numberThree' expects 3 parameters, 1 parameters given");
        \Tester\Assert::exception(function () use ($definition) {
            $definition->process(["one" => "one", "two" => "two"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'numberThree' expects 3 parameters, 2 parameters given");
        \Tester\Assert::exception(function () use ($definition) {
            $definition->process(["one" => "one", "two" => "two", "three" => "three", "four" => "four"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'numberThree' expects 3 parameters, 4 parameters given");
    }

}

(new DefinitionWithNumberOfParametersTest())->run();
