<?php

namespace obo\Tests\Assets\Entities;

class BusinessSubjectManager extends \obo\Tests\Assets\Entities\EntityManager {

    public static function queryCarrier() {
        return parent::queryCarrier()->where("AND {original} IS NULL");
    }

}
