<?php

namespace obo\Tests\Entities;

/**
 * @obo-repositoryName(test)
 * @property int $id
 * @property string $testProperty
 */
class Test extends \obo\Entity {

    /**
     * @obo-run(beforeSave)
     */
    public function firstBeforeSaveCallback() {
        $this->testProperty .= "first";
    }

    /**
     * @obo-run('beforeSave')
     */
    public function secondBeforeSaveCallback() {
        $this->testProperty .= "Second";
    }

    /**
     * @obo-run("beforeSave")
     */
    public function thirdBeforeSaveCallback() {
        $this->testProperty .= "Third";
    }
}
