<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Exceptions;

class Exception extends \Exception {
    public $foreseenFileError = null;
    public $foreseenLineError = null;

    function __construct($message = null, $code = null, $previous = null, $foreseenFileError = null, $foreseenLineError = null) {
        parent::__construct($message, $code, $previous);
        if($foreseenFileError === null OR $foreseenLineError === null) {
            $this->findForeseenPointOfError();
        } else {
            $this->foreseenFileError = $foreseenFileError;
            $this->foreseenLineError = $foreseenLineError;
        }
    }

    protected function findForeseenPointOfError() {
        foreach ($trace = $this->getTrace() as $key => $point) {
            if (!isset($point["class"])) continue;

            if(!\preg_match("#^obo\\\#", $point["class"])) {
                if(isset($trace[$key-1])) {
                    if (isset($trace[$key-1]["file"])) $this->foreseenFileError = $trace[$key-1]["file"];
                    if (isset($trace[$key-1]["line"])) $this->foreseenLineError = $trace[$key-1]["line"];
                }

                break;
            }
        }
    }

}
