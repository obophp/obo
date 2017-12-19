<?php

namespace obo\Tests\Services\EntityInformation\Information\Assets\EntitiesInformation;

class ExposedInformation extends \obo\Services\EntitiesInformation\Information {

    public function exposedCalculateSourcesChangesHash() {
        return $this->calculateChangesHash();
    }

}
