<?php

namespace obo\Interfaces;

interface IEntitiesCollection {

     public function getSubset(\obo\Interfaces\IPaginator $paginator, \obo\Interfaces\IFilter $filter = null);

     public function find(\obo\Carriers\QuerySpecification $specification);
}
