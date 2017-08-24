<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType\Base;

abstract class DataType extends \obo\Object implements \obo\Interfaces\IDataType {

    /**
     * @var \obo\Carriers\PropertyInformationCarrier
     */
    protected $propertyInformation = null;

    /**
     * @var bool
     */
    public $storageDataCompression = false;

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @param array $options
     * @throws \obo\Exceptions\Exception
     */
    public function __construct(\obo\Carriers\PropertyInformationCarrier $propertyInformation, array $options = []) {
        foreach (static::optionsStructure() as $optionName => $requiredOption) {
            if ($requiredOption AND !(isset($options[$optionName]) OR \array_key_exists($optionName, $options))) throw new \obo\Exceptions\Exception("Options don't contain one or more required option from  [" . \implode(", ", \array_keys(\array_filter(static::optionsStructure(), function ($value) {
                    return $value;
                }))) . "]");
        }

        foreach ($options as $optionName => $optionValue) {
            if (!(isset(static::optionsStructure()[$optionName]) OR \array_key_exists($optionName, static::optionsStructure()))) throw new \obo\Exceptions\Exception("Option '{$optionName}' can not be set, it is not supported by the '". static::name() . "' data type");
            $this->$optionName = $optionValue;
        }

        $this->propertyInformation = $propertyInformation;

        foreach ($options as $optionName => $optionValue) {
            if ($optionName === "storageCompression" && $optionValue === true && $this->storageDataCompression() === false) throw new \obo\Exceptions\Exception("Data type '" . static::name() . "' does not support data compression.");
            $this->$optionName = $optionValue;
        }

    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @param array $options
     * @return \obo\Interfaces\IDataType
     */
    public static function createDatatype(\obo\Carriers\PropertyInformationCarrier $propertyInformation, array $options) {
        return new static($propertyInformation, $options);
    }

    /**
     * @return array
     */
    public static function optionsStructure() {
        return ["storageDataCompression" => false];
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function convertValue($value) {
        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function sanitizeValue($value) {
        return $value;
    }

    /**
     * @return void
     */
    public function registerEvents() {

    }

}
