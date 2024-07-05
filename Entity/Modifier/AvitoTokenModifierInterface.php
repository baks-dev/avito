<?php

namespace BaksDev\Avito\Entity\Modifier;

use BaksDev\Core\Type\Modify\ModifyAction;

interface AvitoTokenModifierInterface
{
    public function getAction(): ModifyAction;
}
