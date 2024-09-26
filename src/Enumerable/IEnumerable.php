<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Enumerable;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Interfaces\ICoreObject;
use Pst\Core\Enumerable\Linq\IEnumerableLinq;

use Iterator;
use Traversable;

interface IEnumerable extends ICoreObject, Traversable, IEnumerableLinq {
    public function T(): ITypeHint;
    public function TKey(): ITypeHint;

    public function getIterator(): Iterator;
}