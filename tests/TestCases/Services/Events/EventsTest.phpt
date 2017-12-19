<?php

namespace obo\Tests\Services\Events;

use Tester\Assert;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @testCase
 */
class EventsTest extends \Tester\TestCase {

    private static $contactData = [
        "name" => "John Doe"
    ];

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function getContact() {
        return \obo\Tests\Assets\Entities\Contacts\ContactManager::entityFromArray(static::$contactData);
    }

    public function testBeforeSave() {
        $contact = $this->getContact();

        $contact->on("beforeSave", function($arguments){
            $arguments["entity"]->note .= "first";
        });

        $contact->on("beforeSave", function($arguments){
            $arguments["entity"]->note .= "Second";
        });

        $contact->on("beforeSave", function($arguments){
            $arguments["entity"]->note .= "Third";
        });

        $contact->save();

        \Tester\Assert::same("firstSecondThird", $contact->note);
    }

    public function testOnAndNotify() {
        $contact = $this->getContact();
        $contactClassname = $contact->className();
        $contact->on("event1", function() {return true;});
        $contactClassname::on("event2", function() {return true;});

        \Tester\Assert::true(\current($contact->notifyEvent("event1")));
        \Tester\Assert::true(\current($contact->notifyEvent("event2")));
    }

}

$testCase = new EventsTest();
$testCase->run();
