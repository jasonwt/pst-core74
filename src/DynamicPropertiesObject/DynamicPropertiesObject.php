<?php

declare(strict_types=1);

namespace Pst\Core\DynamicPropertiesObject;

use Pst\Core\CoreObject;

abstract class DynamicPropertiesObject extends CoreObject implements IDynamicPropertiesObject {
    use DynamicPropertiesObjectTrait;
}