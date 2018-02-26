<?php

namespace obo\Tests\TestCases\Entity;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

/**
 * @testCase
 */
class DiscardChangesTest extends \Tester\TestCase {

    protected $noteDefaultData = [];
    protected $noteData = ["id" => 1, "text" => "note text"];
    protected $noteNewText = "new note text";

    public function __construct() {
        $entityPrototype = \obo\Tests\TestCases\Entity\Assets\Entities\Notes\NoteManager::note([]);
        $this->noteDefaultData = $entityPrototype->propertiesAsArray(["text" => true]);
    }

    public function testOnNewEntity() {
        $note = \obo\Tests\TestCases\Entity\Assets\Entities\Notes\NoteManager::note($this->noteData);
        $note->text = $this->noteNewText;

        Assert::same($this->noteNewText, $note->text);

        $note->discardChanges();

        Assert::same($this->noteDefaultData, $note->propertiesAsArray($this->noteDefaultData));
    }

    public function testOnPersistedEntity() {
        \obo\Tests\TestCases\Entity\Assets\Entities\Notes\NoteManager::setDataStorage($this->createDataStorageMockForTestOnPersistedEntity());
        $note = \obo\Tests\TestCases\Entity\Assets\Entities\Notes\NoteManager::note(1);
        $note->text = $this->noteNewText;

        Assert::same($this->noteNewText, $note->text);

        $note->discardChanges();

        Assert::same($this->noteData, $note->propertiesAsArray($this->noteData));
    }

    protected function createDataStorageMockForTestOnPersistedEntity() {
        $specification = \obo\Tests\TestCases\Entity\Assets\Entities\Notes\NoteManager::queryCarrier()
            ->select(\obo\Tests\TestCases\Entity\Assets\Entities\Notes\NoteManager::constructSelect())
            ->where("{id} = ?", 1);

        $dataStorageMock = \Mockery::mock(new \obo\Tests\Assets\DataStorage);

        $dataStorageMock->shouldReceive("dataForQuery")
            ->with(\equalTo($specification))
            ->andReturn([$this->noteData]);

        $dataStorageMock->setDefaultDataForQueryBehavior($dataStorageMock);

        return $dataStorageMock;
    }

}

(new DiscardChangesTest())->run();
