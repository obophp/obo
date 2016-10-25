<?php

namespace obo\Tests\Assets\Entities\Contacts\AdditionalInformation;

class EmailManager extends \obo\Tests\Assets\Entities\Contacts\AdditionalInformationManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email
     */
    public static function email($specification) {
        return parent::entity($specification);
    }

    /**
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email[]
     */
    public static function emails(\obo\Interfaces\IPaginator $paginator = null, \obo\Interfaces\IFilter $filter = null) {
        return parent::findEntities(\obo\Carriers\QueryCarrier::instance(), $paginator, $filter);
    }

}
