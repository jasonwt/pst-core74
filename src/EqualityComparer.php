<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;

abstract class EqualityComparer extends CoreObject implements IEqualityComparer {
    public static function default(?ITypeHint $T = null): IEqualityComparer {
        return new class($T) extends CoreObject implements IEqualityComparer {
            private ITypeHint $T;
            private Func $equals;

            public function __construct(ITypeHint $T) {
                $this->T = $T;

                $compareFunc = function($x, $y): bool {
                    return $x === $y;
                };
                

                $this->equals = Func::new($compareFunc, $T, $T, TypeHintFactory::tryParse("bool"));
            }

            public function equals($x, $y): bool {
                return ($this->equals)($x, $y);
                //return $this->T->equals($x, $y);
            }
        };
    }
}