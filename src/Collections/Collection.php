<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\CoreObject;
use Pst\Core\Types\ITypeHint;

use Pst\Core\Collections\Traits\CollectionTrait;

class Collection extends CoreObject implements ICollection {
    use CollectionTrait;

    public static function new($iterable, ?ITypeHint $T = null): IReadOnlyCollection {
        return new Collection($iterable, $T);
    }
}