<?php

namespace obo\Tests;

use Tester\Assert;

require __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php";

/**
 * @testCase
 */
class EntitiesCollectionTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_ID = 1;

    private static $contactData = [
        "name" => "John Doe"
    ];

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function getContact() {
        return Assets\Entities\Contacts\ContactManager::entityFromArray(static::$contactData);
    }

    public function testCount() {
        $contact = $this->getContact();
        $contact->save();
        Assert::same($contact->phones->count(), 0);
        $contact->phones->addNew(["value" => "777777777"]);
        Assert::same($contact->phones->count(), 1);
        $contact->phones->addNew(["value" => "888888888"]);
        Assert::same($contact->phones->count(), 2);
    }

}

$testCase = new EntitiesCollectionTest();
$testCase->run();
