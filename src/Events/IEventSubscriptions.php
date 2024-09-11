<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Events;

use Closure;

/**
 * Represents a collection of event subscriptions.
 */
interface IEventSubscriptions {
    public function attach(Closure $callback, int $priority = 0);
    public function detach($handler);
}