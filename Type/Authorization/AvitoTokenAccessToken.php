<?php

namespace BaksDev\Avito\Type\Api;

final class AvitoTokenAccessToken
{
    private bool $active = false;

    public function isActive(): bool
    {
        return $this->active;
    }
}