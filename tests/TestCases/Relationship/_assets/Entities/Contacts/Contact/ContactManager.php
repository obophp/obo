<?php

namespace obo\Tests\TestCases\Relationship\Assets\Entities\Contacts;

class ContactManager extends \obo\Tests\Assets\Entities\Contacts\ContactManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\Contact
     */
    public static function contact($specification) {
        return parent::contact($specification);
    }

}
