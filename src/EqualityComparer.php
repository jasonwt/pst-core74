<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Interfaces\IEqualityComparer;

abstract class EqualityComparer extends CoreObject implements IEqualityComparer {
    public static function default(?ITypeHint $T = null, ?ITypeHint $TOther = null): IEqualityComparer {
        return new class($T, $TOther) extends CoreObject implements IEqualityComparer {
            private ITypeHint $T;
            private ITypeHint $TOther;

            private Func $equals;

            public function __construct(ITypeHint $T, ?ITypeHint $TOther = null) {
                $this->TOther ??= ($T ?? TypeHintFactory::undefined());
                $this->T ??= $this->TOther;

                $compareFunc = function($x, $y): bool {
                    return $x === $y;
                };
                

                $this->equals = Func::new($compareFunc, $this->T, $this->TOther, TypeHintFactory::tryParseTypeName("bool"));
            }

            public function equals($x, $y): bool {
                return ($this->equals)($x, $y);
                //return $this->T->equals($x, $y);
            }
        };
    }
}