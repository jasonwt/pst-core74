<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\CoreObject;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Collections\CollectionTrait;
use Pst\Core\Enumerable\Enumerator;

use Iterator;


class Collection extends CoreObject implements Iterator, ICollection {
    use CollectionTrait;

    public function toArray(): array {
        while ($this->collectionTraitItems === null) {
            $this->populateNext();
        }

        return $this->collectionTraitItems;
    }

    public function toCollection(): ICollection {
        return Collection::new($this->toArray(), $this->T());
    }

    public function toReadonlyCollection(): IReadonlyCollection {
        return ReadonlyCollection::new($this->toArray(), $this->T());
    }

    public static function new($iterable, ?ITypeHint $T = null): ICollection {
        return new Collection(Enumerator::new($iterable), $T);
    }

    public static function empty(?ITypeHint $T = null): ICollection {
        return new Collection([], $T);
    }
}