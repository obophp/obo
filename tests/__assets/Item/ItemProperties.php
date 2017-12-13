<?php

namespace obo\Tests\Assets;

class ItemProperties extends \obo\EntityProperties {

    /**
     * @obo-dataType(string)
     * @obo-columnName(id)
     */
    public $id = "";

    /**
     * @obo-dataType(string)
     * @obo-columnName(text)
     */
    public $text = "";
}
