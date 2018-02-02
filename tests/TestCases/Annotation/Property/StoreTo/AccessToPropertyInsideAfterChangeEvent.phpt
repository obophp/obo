<?php

namespace obo\Tests\TestCases\Annotation\Property\StoreTo;

use Tester\Assert;

require __DIR__ . "/../../../../bootstrap.php";

class AccessToPropertyInsideAfterChangeEvent extends \Tester\TestCase {
    
    public function testAccessToPropertyInsideAfterChangeEvent() {
        \obo\Tests\TestCases\Annotation\Property\StoreTo\Assets\Entities\Notes\NoteManager::setDataStorage($this->createDataStorageMockForTestAccessToPropertyInsideAfterChangeEvent());
        $note = \obo\Tests\TestCases\Annotation\Property\StoreTo\Assets\Entities\Notes\NoteManager::note(1);
        $note->changeValuesPropertiesFromArray(["language" => "cs"]);

        Assert::same(["metadata" => ["language" => "cs"]], $note->dataToStore());
    }

    protected function createDataStorageMockForTestAccessToPropertyInsideAfterChangeEvent() {
        $specification = \obo\Tests\TestCases\Annotation\Property\StoreTo\Assets\Entities\Notes\NoteManager::queryCarrier()
            ->select(\obo\Tests\TestCases\Annotation\Property\StoreTo\Assets\Entities\Notes\NoteManager::constructSelect())
            ->where("{id} = ?", 1);

        $dataStorageMock = \Mockery::mock(new \obo\Tests\Assets\DataStorage);

        $dataStorageMock->shouldReceive("dataForQuery")
            ->with(\equalTo($specification))
            ->andReturn([[
                "id" => 1,
                "text" => "note text",
                "metadata" => ["language" => "en"]
            ]]);

        $dataStorageMock->setDefaultDataForQueryBehavior($dataStorageMock);

        return $dataStorageMock;
    }

}

(new AccessToPropertyInsideAfterChangeEvent())->run();
