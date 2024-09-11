<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Events;

/**
 * Represents an event.
 */
interface IEvent extends IEventSubscriptions{
    public function invoke(...$args);
}