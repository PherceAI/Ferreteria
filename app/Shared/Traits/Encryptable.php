<?php

declare(strict_types=1);

namespace App\Shared\Traits;

trait Encryptable
{
    public function initializeEncryptable(): void
    {
        $casts = method_exists($this, 'casts') ? $this->casts() : $this->casts;
        $encryptable = property_exists($this, 'encryptable') ? $this->encryptable : [];

        foreach ($encryptable as $attribute) {
            if (! array_key_exists($attribute, $casts)) {
                $casts[$attribute] = 'encrypted';
            }
        }

        $this->casts = $casts;
    }
}
