<?php

if (@!include __DIR__ . "/../vendor/autoload.php") {
    echo "Install Nette Tester using `composer update`";
    exit(1);
}

Tester\Environment::setup();
date_default_timezone_set("Europe/Prague");

\Tracy\Debugger::$logDirectory = __DIR__ . "/log";

$_prevEx = set_exception_handler(function($e) use (& $_prevEx) {
    if (!$e instanceof \Tester\AssertException) \Tracy\Debugger::log($e, \Tracy\ILogger::EXCEPTION);

    if ($_prevEx !== null) {
        $_prevEx($e);
    }
});

$_prevEr = set_error_handler(function($severity, $message, $file, $line) use (& $_prevEr) {
    if (error_reporting() === 0) return false;
    $e = new \ErrorException($message, 0, $severity, $file, $line);

    \Tracy\Debugger::log($e, \Tracy\ILogger::EXCEPTION);

    if ($_prevEr === null) {
        return false;
    } else {
        call_user_func_array($_prevEr, func_get_args());
    }
});

\obo\obo::$developerMode = true;
\obo\obo::setCache(new \obo\Tests\Assets\Cache(__DIR__ . "/temp"));
\obo\obo::setTempDir(__DIR__ . "/temp");
\obo\obo::addModelsDirs([
   __DIR__ . "/",
]);

\obo\obo::setDefaultDataStorage(\obo\Tests\Assets\DefaultDataStorageFactory::createDataStorage());
\obo\obo::run();

function test(\Closure $function) {
    $function();
}
