<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class DataCarrier extends \obo\Object implements \Iterator,  \ArrayAccess, \Countable {

    private $variables = array();

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
       if (isset($this->variables[$name])) return $this->variables[$name];
       throw new \obo\Exceptions\VariableNotFoundException("Variable with name '".$name."' does not exist");
    }

    /**
     * @return array
     */
    public function asArray() {
        return $this->variables;
    }

    /**
     * @param array $variables
     * @return void
     */
    public function setVariables(array $variables) {
        $this->variables = $variables;
    }

    /**
     * @param mixed $variable
     * @param string $variableName
     * @return void
     */
    public function setValueForVariableWithName($variable, $variableName) {
        $this->variables[$variableName] = $variable;
    }

    /**
     * @param string $varibleName
     * @return void
     */
    public function unsetValueForVaraibleWithName($varibleName) {
        $this->variableForName($varibleName);
        unset($this->variables[$varibleName]);

    }

    /**
     * @param array $data
     * @return void
     */
    public function __construct($data = array()) {
        foreach ($data as $variableName => $variableValue) $this->$variableName = $variableValue;
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
     * @param type $name
     * @return void
     */
    public function __unset($name) {
        $this->unsetValueForVaraibleWithName($name);
    }

    /**
     *
     * @param type $name
     * @return boolean
     */
    public function __isset($name) {
        $variables = $this->variables();
        return isset($variables[$name]);
    }

    /**
     * @return void
     */
    public function __clone() {
        $data = array();
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
     * @return boolean
     */
    public function valid() {
        return isset($this->variables[$this->key()]);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->variables[] = $value;
        } else {
            $this->variables[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return isset($this->variables[$offset]);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->variables[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        if (isset($this->variables[$offset])) return $this->variables[$offset];
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