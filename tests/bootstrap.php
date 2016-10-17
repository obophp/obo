<?php

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .
if (@!include __DIR__ . "/../vendor/autoload.php") {
    echo "Install Nette Tester using `composer update`";
    exit(1);
}

require_once __DIR__ . '/__assets/Storage.php';

// configure environment
Tester\Environment::setup();
date_default_timezone_set("Europe/Prague");

\obo\obo::$developerMode = true;
\obo\obo::setCache(new obo\Tests\Assets\Cache(__DIR__ . DIRECTORY_SEPARATOR . "temp"));
\obo\obo::setTempDir(__DIR__ . DIRECTORY_SEPARATOR . "temp");
\obo\obo::addModelsDirs([
   __DIR__ . DIRECTORY_SEPARATOR . "__assets" . DIRECTORY_SEPARATOR . "Entities",
]);

\obo\obo::setDefaultDataStorage(\obo\Tests\Assets\Storage::getMockDataStorage());
\obo\obo::run();

function test(\Closure $function) {
    $function();
}
