<?php

namespace obo\Tests;

use Tester\Assert;

require __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php";

class EventsTest extends \Tester\TestCase {

    private static $contactData = [
        "name" => "John Doe"
    ];

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function getContact() {
        return Assets\Entities\Contacts\ContactManager::entityFromArray(static::$contactData);
    }

    public function testBeforeSave() {
        $contact = $this->getContact();
        $contact->save();
        Assert::same("firstSecondThird", $contact->note);
    }

}

$testCase = new EventsTest();
$testCase->run();
