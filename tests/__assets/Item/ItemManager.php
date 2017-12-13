<?php

namespace obo\Tests\Assets;

class ItemManager extends \obo\EntityManager {

    public static function item($specification) {
        return static::entity($specification);
    }

}
