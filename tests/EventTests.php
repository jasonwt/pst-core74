<?php

/*TDD*/

declare(strict_types=1);

namespace EventTests;

use Pst\Core\Types\Type;
use Pst\Core\Types\TypeHint;

use Pst\Core\Events\Event;
use Pst\Core\Events\IEventSubscriptions;

use Pst\Testing\Should;

use InvalidArgumentException;
use Exception;

require_once(__DIR__ . "/../vendor/autoload.php");

Should::executeTests(function() {
    class SomeClass {
        protected Event $event1;
        protected Event $event2;

        public function __construct() {
            $this->event1 = new Event($this);
            $this->event2 = new Event($this, TypeHint::undefined(), Type::float());
        }

        public function event1(): IEventSubscriptions {
            return $this->event1;
        }

        public function event2(): IEventSubscriptions {
            return $this->event2;
        }

        protected function onEvent1(... $args) {
            $this->event1->invoke(... $args);
        }

        protected function onEvent2(... $args) {
            $this->event2->invoke(... $args);
        }

        public function execute() {
            $this->onEvent1();
            $this->onEvent2("Hello, world!", 3.14);
        }
    }

    $event1Triggered = false;
    $event2Triggered = false;

    $closure1 = function(object $sender) use (&$event1Triggered): void {
        $event1Triggered = true;
    };

    $closure2 = function(object $sender, string $a, float $b) use (&$event2Triggered): void {
        $event2Triggered = true;
    };

    $someClass = new SomeClass();

    Should::throw(InvalidArgumentException::class, fn() => $someClass->event1()->attach(function(int $sender): void {}));
    Should::throw(InvalidArgumentException::class, fn() => $someClass->event1()->attach(function($sender, $a): void {}));
    Should::notThrow(InvalidArgumentException::class, fn() => $someClass->event1()->attach(function(object $sender): void {}));

    Should::notThrow(Exception::class, fn() => $someClass->event1()->attach($closure1));
    // Fail because instance of closure1 has already been registered
    Should::throw(InvalidArgumentException::class, fn() => $someClass->event1()->attach($closure1)); 

    Should::notThrow(Exception::class, fn() => $someClass->event2()->attach($closure2));
    // Fail because instance of closure2 has already been registered
    Should::throw(InvalidArgumentException::class, fn() => $someClass->event2()->attach($closure2));

    Should::equal(false, $event1Triggered);
    Should::equal(false, $event2Triggered);

    $someClass->execute();

    Should::equal(true, $event1Triggered);
    Should::equal(true, $event2Triggered);
});