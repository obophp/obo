<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class DataCarrier extends \obo\Object implements \Iterator,  \ArrayAccess, \Countable {

    /**
     * @var array
     */
    private $variables = [];

    /**
     * @param array $data
     */
    public function __construct($data = []) {
        foreach ($data as $variableName => $variableValue) $this->$variableName = $variableValue;
    }

    /**
     * @return array
     */
    protected function &variables() {
        return $this->variables;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \obo\Exceptions\VariableNotFoundException
     */
    public function &variableForName($name) {
       if (isset($this->variables()[$name])) return $this->variables()[$name];
       throw new \obo\Exceptions\VariableNotFoundException("Variable with name '".$name."' does not exist");
    }

    /**
     * @return array
     */
    public function asArray() {
        return $this->variables();
    }

    /**
     * @param array $variables
     * @return void
     */
    public function setVariables(array $variables) {
        $this->variables = $variables;
    }

    /**
     * @param mixed $value
     * @param string $variableName
     * @return mixed
     */
    public function setValueForVariableWithName($value, $variableName) {
        return $this->variables()[$variableName] = $value;
    }

    /**
     * @param string $variableName
     * @return void
     */
    public function unsetValueForVariableWithName($variableName) {
        $this->variableForName($variableName);
        unset($this->variables()[$variableName]);
    }

    /**
     * @return void
     */
    public function clear() {
        $this->variables = [];
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function containsValue($value) {
        return \array_search($value, $this->variables()) === false ? false : true;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function variableNameForValue($value) {
        return ($oldVariableName = \array_search($value, $this->variables())) === false ? null : $oldVariableName;
    }

    /**
     * @param mixed $newVariableName
     * @param mixed $value
     * @return void
     * @throws \obo\Exceptions\VariableNotFoundException
     */
    public function changeVariableNameForValue($newVariableName, $value) {
        if (($oldVariableName = $this->variableNameForValue($value)) === null) throw new \obo\Exceptions\VariableNotFoundException("Value does not exist in collection");
        $this->unsetValueForVariableWithName($oldVariableName);
        $this->setValueForVariableWithName($value, $newVariableName);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function &__get($name) {
        return $this->variableForName($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value) {
        return $this->setValueForVariableWithName($value, $name);
    }

    /**
     * @param string $name
     * @return void
     */
    public function __unset($name) {
        $this->unsetValueForVariableWithName($name);
    }

    /**
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        return isset($this->variables()[$name]);
    }

    /**
     * @return void
     */
    public function __clone() {
        $data = [];
        foreach ($this->asArray() as $key => $variable) {
            if (\is_object($variable)) {
                $data[$key] = clone $variable;
            } else {
                $data[$key] = $variable;
            }
        }
        $this->setVariables($data);
    }

    /**
     * @return mixed
     */
    public function rewind() {
        return reset($this->variables());
    }

    /**
     * @return mixed
     */
    public function current() {
        return current($this->variables());
    }

    /**
     * @return mixed
     */
    public function key() {
        return key($this->variables());
    }

    /**
     * @return mixed
     */
    public function prev() {
        return \prev($this->variables());
    }

    /**
     * @return mixed
     */
    public function next() {
        return next($this->variables());
    }

    /**
     * @return mixed
     */
    public function end() {
        return end($this->variables());
    }

    /**
     * @return bool
     */
    public function valid() {
        return isset($this->variables()[$this->key()]);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        $this->variables()[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->variables()[$offset]);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->variables()[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        if (isset($this->variables()[$offset])) return $this->variables()[$offset];
        return null;
    }

    /**
     * @return int
     */
    public function count() {
        return \count($this->variables());
    }

    /**
     * @return array
     */
    public function dump() {
        return \print_r($this->variables(), true);
    }
}
