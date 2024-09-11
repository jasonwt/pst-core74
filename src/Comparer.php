<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\Types\TypeHint;
use Pst\Core\Types\ITypeHint;

use Closure;

abstract class Comparer extends CoreObject implements IComparer {
    public static function default(?ITypeHint $T = null): IComparer {
        return new class($T) extends CoreObject implements IComparer {
            private ITypeHint $T;
            private Func $compare;

            public function __construct(ITypeHint $T) {
                $this->T = $T;

                $compareFunc = function($x, $y): int {
                    return $x === $y ? 0 : ($x < $y ? -1 : 1);
                };

                $this->compare = Func::new($compareFunc, $T, $T, TypeHint::fromTypeNames("int"));
            }

            public function compare($x, $y): int {
                return $this->compare($x, $y);
                //return $this->T->equals($x, $y);
            }
        };
    }
}