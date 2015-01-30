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
\obo\obo::run();

function test(\Closure $function) {
    $function();
}
