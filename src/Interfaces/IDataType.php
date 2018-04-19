<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Interfaces;

interface IDataType {

    const DATA_TYPE_CLASS_ARRAY = "array";
    const DATA_TYPE_CLASS_BOOLEAN = "boolean";
    const DATA_TYPE_CLASS_DATETIME = "dateTime";
    const DATA_TYPE_CLASS_DATEINTERVAL = "dateInterval";
    const DATA_TYPE_CLASS_ENTITY = "entity";
    const DATA_TYPE_CLASS_FLOAT = "float";
    const DATA_TYPE_CLASS_INTEGER = "integer";
    const DATA_TYPE_CLASS_MIXED = "mixed";
    const DATA_TYPE_CLASS_OBJECT = "object";
    const DATA_TYPE_CLASS_STRING = "string";

    /**
     * @return string
     */
    public static function name();

    /**
     * @return string Return one of data type class constant.
     */
    public static function dataTypeClass();

    /**
     * @return array Parameter names e.g. ["length" => true, "unsigned" => false].
     */
    public static function optionsStructure();

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @param array $options
     * @return \obo\Interfaces\IDataType The configured instance itself.
     */
    public static function createDatatype(\obo\Carriers\PropertyInformationCarrier $propertyInformation, array $options);

    /**
     * @param mixed $value
     * @param bool $throwException
     * @return bool
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function validate($value, $throwException);

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function convertValue($value);

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function sanitizeValue($value);

    /**
     * @return void
     */
    public function registerEvents();

    /**
     * Is compression in specific dataType allowed?
     * @return boolean
     */
    public function storageDataCompression();

}
