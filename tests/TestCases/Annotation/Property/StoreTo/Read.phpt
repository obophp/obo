<?php

namespace obo\Tests\TestCases\Annotation\Property\StoreTo;

use Tester\Assert;

require __DIR__ . "/../../../../bootstrap.php";

class Read extends \Tester\TestCase {

    public function testRead() {
        $note = \obo\Tests\TestCases\Annotation\Property\StoreTo\Assets\Entities\Notes\NoteManager::note(["metadata" => ["language" => "cs"]]);

        Assert::same("cs", $note->language);
    }

}

(new Read())->run();
