<?php

declare(strict_types=1);

namespace Pst\Core\RichTextBuilder;

class RichTextBuilder extends CoreObject implements IRichTextBuilder{
    private string $value;

    public function __construct(string $value = '') {
        $this->value = $value;
    }
}