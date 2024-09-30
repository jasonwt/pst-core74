<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Enumerable;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Enumerable\Linq\EnumerableLinqTrait;

use Iterator;
use ArrayIterator;
use IteratorAggregate;

use TypeError;
use InvalidArgumentException;

trait EnumerableTrait {
    use EnumerableLinqTrait {}

    private static bool $shouldTypeCheck = true;

    private ITypeHint $T;
    private ITypeHint $TKey;
    private Iterator $iterator;

    /**
     * Creates a new instance of Enumerable
     * 
     * @param iterable $iterator 
     * @param ITypeHint|null $T 
     * 
     * @throws TypeError 
     * @throws InvalidArgumentException 
     */
    public function __construct(iterable $iterator, ?ITypeHint $T = null, ?ITypeHint $TKey = null) {
        if (is_array($iterator)) {
            $this->iterator = new ArrayIterator($iterator);
        } else {
            if ($iterator instanceof IEnumerable) {
                $T ??= $iterator->T();
                $TKey ??= $iterator->TKey();

                if (!$T->isAssignableFrom($iterator->T())) {
                    throw new TypeError("{$T} is not assignable to {$iterator->T()}");
                }

                if (!$TKey->isAssignableFrom($iterator->TKey())) {
                    throw new TypeError("{$TKey} is not assignable to {$iterator->TKey()}");
                }
            }

            while ($iterator instanceof IteratorAggregate) {
                $iterator = $iterator->getIterator();
            }
        }

        $this->T = $T ?? TypeHintFactory::undefined();
        $this->TKey = $TKey ?? TypeHintFactory::keyTypes();

        if (!$this->TKey->isAssignableTo(TypeHintFactory::keyTypes())) {
            throw new TypeError("{$this->TKey} is not assignable to key types");
        }

        $this->iterator = is_array($iterator) ? new ArrayIterator($iterator) : $iterator;
    }

    /**
     * Gets the value type hint
     * 
     * @return ITypeHint 
     */
    public function T(): ITypeHint {
        return $this->T;
    }

    /**
     * Gets the key type hint
     * 
     * @return ITypeHint 
     */
    public function TKey(): ITypeHint {
        return $this->TKey;
    }
    
    /**
     * Gets the iterator
     * 
     * @return Iterator 
     * 
     * @throws TypeError 
     */
    public function getIterator(): Iterator {
        return $this->iterator;
    }
}