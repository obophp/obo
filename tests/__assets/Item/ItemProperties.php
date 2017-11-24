<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace obo\Tests\Assets;

/**
 * Description of ItemProperties
 *
 * @author jirichamrad
 */
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
