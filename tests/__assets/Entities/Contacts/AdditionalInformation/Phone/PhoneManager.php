<?php

namespace obo\Tests\Assets\Entities\Contacts\AdditionalInformation;

class PhoneManager extends \obo\Tests\Assets\Entities\Contacts\AdditionalInformationManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone
     */
    public static function phone($specification) {
        return parent::entity($specification);
    }

    /**
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone[]
     */
    public static function phones($paginator = null, $filter = null) {
        return parent::findEntities(\obo\Carriers\QueryCarrier::instance(), $paginator, $filter);
    }

}
