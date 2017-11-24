<?php

namespace obo\Tests\EntityInformation;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

/**
 * @testCase
 */
class CalculateSourcesChangesHashTest extends \Tester\TestCase {

    const ENVIRONMENT_VERSION = "TEST ENVIRONMENT";
    const ENTITY_FILE_PATH = "/__assets/Entities/Note/Note.php";

    public function testChangeSourceSensitivity() {
        $information = $this->createInformation(static::ENVIRONMENT_VERSION);
        $fileContent = file_get_contents(__DIR__ . self::ENTITY_FILE_PATH);

        $hash1 = $information->exposedCalculateSourcesChangesHash();
        $hash2 = $information->exposedCalculateSourcesChangesHash();

        Assert::same($hash1, $hash2);

        \file_put_contents(__DIR__ . self::ENTITY_FILE_PATH, "CHANGED");
        $hash2 = $information->exposedCalculateSourcesChangesHash();

        Assert::notSame($hash1, $hash2);

        \file_put_contents(__DIR__ . self::ENTITY_FILE_PATH, $fileContent);
    }

    public function testChangePlatformSensitivity() {
        $information1 = $this->createInformation(static::ENVIRONMENT_VERSION);
        $information2 = $this->createInformation(static::ENVIRONMENT_VERSION . " UPDATED");

        Assert::notSame($information1->exposedCalculateSourcesChangesHash(), $information2->exposedCalculateSourcesChangesHash());
    }

    protected function createInformation($environmentVersion) {
        $modelsDirs = [__DIR__ . "/../../__assets/AbstractEntities", __DIR__ . "/../../__assets/Entities",  __DIR__ . "/__assets/Entities"];
        return new \obo\Tests\EntityInformation\Assets\EntitiesInformation\ExposedInformation($modelsDirs, \obo\obo::$entitiesExplorer, \obo\obo::$cache, $environmentVersion);
    }

}

(new CalculateSourcesChangesHashTest())->run();
