<?php

namespace obo\Tests\Assets\Entities\Contacts;

class ContactManager extends \obo\Tests\Assets\Entities\EntityManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    public static function contact($specification) {
        return parent::entity($specification);
    }

    /**
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return \obo\Tests\Assets\Entities\Contacts\Contact[]
     */
    public static function contacts($paginator = null, $filter = null) {
        return parent::findEntities(static::queryCarrier(), $paginator, $filter);
    }

}
