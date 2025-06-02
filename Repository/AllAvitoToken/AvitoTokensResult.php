<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Avito\Repository\AllAvitoToken;

use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use Symfony\Component\Validator\Constraints as Assert;

/** @see AvitoTokensResult */
final class AvitoTokensResult
{

    public function __construct(
        private readonly string $id, // => "0188a9a8-7508-7b3e-a0a1-312e03f7bdd9"
        private readonly string $event, //" => "5d8bfeba-a2e7-4886-ae98-5ec326cc516a"

        private readonly string $users_profile_event, //" => "0188a9a8-54c9-716f-9297-94a6348291a5"
        private readonly string $users_profile_status, //" => "mod"
        private readonly mixed $users_profile_username, //" => null

        private readonly bool|null $users_profile_avatar, //" => null
        private readonly bool|null $users_profile_avatar_ext, //" => null
        private readonly bool|null $users_profile_avatar_cdn, //" => null

        private readonly mixed $account_email, //" => null
        private readonly mixed $account_status, //" => null

        private readonly mixed $active, //" => null

    ) {}

    public function getId(): UserProfileUid
    {
        return new UserProfileUid($this->id);
    }

    public function getEvent(): AvitoTokenEventUid
    {
        return new AvitoTokenEventUid($this->event);
    }

    public function getUsersProfileEvent(): UserProfileEventUid
    {
        return new UserProfileEventUid($this->users_profile_event);
    }

    public function getUsersProfileStatus(): UserProfileStatus
    {
        return new UserProfileStatus($this->users_profile_status);
    }

    /**
     * UsersProfileUsername
     */
    public function getUsersProfileUsername()
    {
        return $this->users_profile_username;
    }

    public function getUsersProfileAvatar(): ?bool
    {
        return $this->users_profile_avatar;
    }

    public function getUsersProfileAvatarExt(): ?bool
    {
        return $this->users_profile_avatar_ext;
    }

    public function getUsersProfileAvatarCdn(): ?bool
    {
        return $this->users_profile_avatar_cdn;
    }

    /**
     * AccountEmail
     */
    public function getAccountEmail()
    {
        return $this->account_email;
    }

    /**
     * AccountStatus
     */
    public function getAccountStatus()
    {
        return $this->account_status;
    }

    /**
     * Active
     */
    public function getActive()
    {
        return $this->active;
    }


}