<?php

namespace BaksDev\Avito\Security;

use BaksDev\Users\Profile\Group\Security\RoleInterface;
use BaksDev\Users\Profile\Group\Security\VoterInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.security.voter')]
class VoterActive implements VoterInterface
{
    public const string VOTER = 'ACTIVE';

    public static function getVoter(): string
    {
        return Role::ROLE.'_'.self::VOTER;
    }

    public function equals(RoleInterface $role): bool
    {
        return $role->getRole() === Role::ROLE;
    }
}
