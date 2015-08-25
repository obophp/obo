<?php

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .
if (@!include __DIR__ . "/../vendor/autoload.php") {
    echo "Install Nette Tester using `composer update`";
    exit(1);
}

// configure environment
Tester\Environment::setup();
date_default_timezone_set("Europe/Prague");

\obo\obo::$developerMode = true;
\obo\obo::setDefaultDataStorage(new \obo\DataStorage\MemoryStorage([
    "test" => [
        "id",
        "testProperty",
    ]
]));
\obo\obo::setCache(new Obo\Tests\Cache(__DIR__ . DIRECTORY_SEPARATOR . "temp"));
\obo\obo::setTempDir(__DIR__ . DIRECTORY_SEPARATOR . "temp");
\obo\obo::addModelsDirs([
   __DIR__ . DIRECTORY_SEPARATOR . "obo" . DIRECTORY_SEPARATOR . "Entities",
]);
\obo\obo::run();

function test(\Closure $function) {
    $function();
}
