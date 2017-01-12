<?php

namespace obo\Tests\Assets\AbstractEntities\Contacts;

abstract class AddressManager extends \obo\Tests\Assets\AbstractEntities\EntityManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\Assets\AbstractEntities\Contacts\Address
     */
    public static function address($specification) {
        return parent::entity($specification);
    }

    /**
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return \obo\Tests\Assets\AbstractEntities\Contacts\Address[]
     */
    public static function addresses($paginator = null, $filter = null) {
        return parent::findEntities(\obo\Carriers\QueryCarrier::instance(), $paginator, $filter);
    }

}
