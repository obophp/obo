<?php

namespace obo\Tests\TestCases\Annotation\Base;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @testCase
 */
class DefinitionWithParametersTest extends \Tester\TestCase {

    protected function prepareDefinition($name, $value, $scope = "entity") {
        \obo\Tests\TestCases\Annotation\Base\Assets\Definitions\DefinitionWithParameters::$name = $name;
        \obo\Tests\TestCases\Annotation\Base\Assets\Definitions\DefinitionWithParameters::$scope = $scope;
        \obo\Tests\TestCases\Annotation\Base\Assets\Definitions\DefinitionWithParameters::$value = $value;
        return new \obo\Tests\TestCases\Annotation\Base\Assets\Definitions\DefinitionWithParameters(new \obo\Carriers\EntityInformationCarrier());
    }

    public function testWithoutParams() {
        \Tester\Assert::null($this->prepareDefinition("without", [])->process([]));
    }

    public function testWithoutParamsWrong() {
        \Tester\Assert::exception(function () {
            $this->prepareDefinition("without", [])->process(["oneParam" => "oneValue"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'without' does not accept parameter with name 'oneParam'");
    }

    public function testRequiredParams() {
        \Tester\Assert::null($this->prepareDefinition("required", ["one" => true])->process(["one" => "one"]));
        \Tester\Assert::null($this->prepareDefinition("required", ["one" => true, "two" => true])->process(["one" => "one", "two" => "two"]));
    }

    public function testRequiredParamsWrong() {
        $definition = $this->prepareDefinition("required", ["one" => true]);

        \Tester\Assert::exception(function () use ($definition) {
            $definition->process([]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'required' requires a parameter with name 'one' which was not sent");
        \Tester\Assert::exception(function () use ($definition) {
            $definition->process(["two" => "two"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'required' does not accept parameter with name 'two'");

        $definition = $this->prepareDefinition("required", ["one" => true, "two" => true]);

        \Tester\Assert::exception(function () use ($definition) {
            $definition->process(["one" => "one"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'required' requires a parameter with name 'two' which was not sent");
        \Tester\Assert::exception(function () use ($definition) {
            $definition->process(["two" => "two"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'required' requires a parameter with name 'one' which was not sent");
        \Tester\Assert::exception(function () use ($definition) {
            $definition->process(["one" => "one", "two" => "two", "three" => "three"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'required' does not accept parameter with name 'three'");
    }

    public function testOptionalParams() {
        $definition = $this->prepareDefinition("optional", ["one" => false]);

        \Tester\Assert::null($definition->process([]));
        \Tester\Assert::null($definition->process(["one" => "one"]));

        $definition = $this->prepareDefinition("optional", ["one" => false, "two" => false]);

        \Tester\Assert::null($definition->process([]));
        \Tester\Assert::null($definition->process(["one" => "one"]));
        \Tester\Assert::null($definition->process(["two" => "two"]));
        \Tester\Assert::null($definition->process(["two" => "two", "one" => "one"]));
    }

    public function testMixedParams() {
        $definition = $this->prepareDefinition("mixed", ["one" => false, "two" => true, "three" => false]);

        \Tester\Assert::null($definition->process(["two" => "two"]));
        \Tester\Assert::null($definition->process(["one" => "one", "two" => "two", "three" => "three"]));
        \Tester\Assert::exception(function () use ($definition) {
            $definition->process(["one" => "one", "three" => "three"]);
        }, \obo\Exceptions\BadAnnotationException::class, "Annotation with name 'mixed' requires a parameter with name 'two' which was not sent");
    }

}

(new DefinitionWithParametersTest())->run();
