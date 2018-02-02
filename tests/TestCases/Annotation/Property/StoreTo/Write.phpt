<?php

namespace obo\Tests\TestCases\Annotation\Property\StoreTo;

use Tester\Assert;

require __DIR__ . "/../../../../bootstrap.php";

class Write extends \Tester\TestCase {

    public function testWrite() {
        $note = \obo\Tests\TestCases\Annotation\Property\StoreTo\Assets\Entities\Notes\NoteManager::note(["language" => "cs"]);
        $note->changeValuesPropertiesFromArray(["language" => "cs"]);

        Assert::same(["language" => "cs"], $note->metadata);
    }

}

(new Write())->run();
