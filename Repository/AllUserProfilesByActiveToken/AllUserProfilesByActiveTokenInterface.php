<?php

namespace BaksDev\Avito\Repository\AllUserProfilesByActiveToken;

interface AllUserProfilesByActiveTokenInterface
{
    public function findProfilesByActiveToken(): \Generator;
}
