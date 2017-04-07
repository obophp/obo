<?php

namespace obo\Tests\TestCases\Annotation\Base\Assets\Definitions;

abstract class TestDefinition extends \obo\Annotation\Base\Definition {

    public static $scope;

    public static $name;

    public static function name() {
        return static::$name;
    }

    public static function scope() {
        return static::$scope;
    }

}
