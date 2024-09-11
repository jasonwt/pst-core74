<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Events;

use Pst\Core\Action;

use Pst\Core\Types\TypeHint;
use Pst\Core\Types\ITypeHint;

use Closure;
use InvalidArgumentException;


/**
 * Represents an event.
 * 
 * @package PST\Core
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 * 
 */
class Event implements IEvent {
    private object $sender;
    private array $handlers = [];
    private array $eventArgumentTypes = [];

    /**
     * Creates a new Event instance.
     * 
     * @param object $sender The sender of the event.
     * @param ITypeHint ...$eventArgumentTypes The types of the event arguments.
     */
    public function __construct(object $sender, ITypeHint ...$eventArgumentTypes) {
        $this->sender = $sender;
        $this->eventArgumentTypes = array_merge([TypeHint::object()], $eventArgumentTypes);
    }

    /**
     * Attaches a handler to the event.
     * 
     * @param Closure $handler The handler to attach.
     * @param int $priority The priority of the handler.
     * 
     * @return int The handler ID.
     * 
     * @throws InvalidArgumentException
     */
    public function attach(Closure $handler, int $priority = 0): int {
        $handlerId = spl_object_id($handler);

        if (isset($this->handlers[$handlerId])) {
            throw new InvalidArgumentException("The handler already exists.");
        }

        Action::new($handler, ...$this->eventArgumentTypes);

        $this->handlers[$handlerId] = [
            "handler" => $handler,
            "priority" => $priority
        ];

        // sort this->handlers by priority while keeping the keys
        uasort($this->handlers, function($a, $b) {
            return $a["priority"] <=> $b["priority"];
        });

        return $handlerId;
    }

    /**
     * Detaches a handler from the event.
     * 
     * @param int|Closure $handler 
     * 
     * @return void 
     * 
     * @throws InvalidArgumentException
     */
    public function detach($handler): void {
        if ($handler instanceof Closure) {
            $handler = spl_object_id($handler);
        }

        if (!is_int($handler)) {
            throw new InvalidArgumentException("The handler must be a closure or an integer.");
        }

        if (!isset($this->handlers[$handler])) {
            throw new InvalidArgumentException("The handler does not exist.");
        }

        unset($this->handlers[$handler]);
    }

    /**
     * Invokes the event.
     * 
     * @param mixed ...$args The arguments to pass to the handlers.
     * 
     * @return void 
     * 
     * @throws InvalidArgumentException
     */
    public function invoke(...$args): void {
        if (count($args) !== count($this->eventArgumentTypes) - 1) {
            throw new InvalidArgumentException("The number of arguments does not match the number of event arguments.");
        }

        $args = array_merge([$this->sender], $args);

        foreach ($this->handlers as $handler) {
            $handler["handler"](...$args);
        }
    }
}