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

namespace BaksDev\Avito\Type\Id;

use BaksDev\Core\Type\UidType\Uid;
use JsonException;
use Symfony\Component\Uid\AbstractUid;


final class AvitoTokenUid extends Uid
{
    public const string TEST = '9a78f1e0-e196-72c9-83ac-0c32faddfe60';

    public const string TYPE = 'avito_token';

    private mixed $attr;

    public function __construct(
        AbstractUid|string|null $value = null,
        string|null $attr = null,
    )
    {
        parent::__construct($value);
        $this->attr = $attr;
    }

    /**
     * @throws JsonException
     */
    public function getAttr(): object|false
    {
        if(empty($this->attr))
        {
            return false;
        }

        if(false === json_validate($this->attr))
        {
            return false;
        }

        return json_decode($this->attr, false, 512, JSON_THROW_ON_ERROR);
    }
}