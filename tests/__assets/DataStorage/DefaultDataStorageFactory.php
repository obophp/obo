<?php

namespace obo\Tests\Assets;

class DefaultDataStorageFactory {

    public static function createDataStorage() {
        return new \obo\Tests\Assets\DataStorage();
    }

}
