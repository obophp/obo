<?php

namespace obo\Tests\EntityInformation\Assets\EntitiesInformation;

class ExposedInformation extends \obo\Services\EntitiesInformation\Information {

    public function exposedCalculateSourcesChangesHash() {
        return $this->calculateChangesHash();
    }

}
