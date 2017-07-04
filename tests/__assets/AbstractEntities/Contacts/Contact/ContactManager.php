<?php

namespace obo\Tests\Assets\AbstractEntities\Contacts;

abstract class ContactManager extends \obo\Tests\Assets\AbstractEntities\EntityManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\Assets\AbstractEntities\Contacts\Contact
     */
    public static function contact($specification) {
        return static::entity($specification);
    }

    /**
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return \obo\Tests\Assets\AbstractEntities\Contacts\Contact[]
     */
    public static function contacts($paginator = null, $filter = null) {
        return static::findEntities(static::queryCarrier(), $paginator, $filter);
    }

}
