<?php

namespace obo\Tests\TestCases\Annotation\Base\Assets\Definitions;

class DefinitionWithParameters extends \obo\Tests\TestCases\Annotation\Base\Assets\Definitions\TestDefinition {

    public static $value;

    public static function parametersDefinition() {
        return [static::PARAMETERS_DEFINITION => static::$value];
    }

}
