<?php

namespace BaksDev\Avito\Repository\AllAvitoToken;

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

interface AllAvitoTokenInterface
{
    public function search(SearchDTO $search): self;

    public function profile(UserProfileUid|string $profile): self;

    public function findAll(): PaginatorInterface;
}