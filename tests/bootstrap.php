<?php

if (@!include __DIR__ . "/../vendor/autoload.php") {
    echo "Install Nette Tester using `composer update`";
    exit(1);
}

Tester\Environment::setup();
date_default_timezone_set("Europe/Prague");

\obo\obo::$developerMode = true;
\obo\obo::setCache(new obo\Tests\Assets\Cache(__DIR__ . "/temp"));
\obo\obo::setTempDir(__DIR__ . "/temp");
\obo\obo::addModelsDirs([
   __DIR__ . "/",
]);

\obo\obo::setDefaultDataStorage(\obo\Tests\Assets\DefaultDataStorageFactory::createDataStorage());
\obo\obo::run();

function test(\Closure $function) {
    $function();
}
