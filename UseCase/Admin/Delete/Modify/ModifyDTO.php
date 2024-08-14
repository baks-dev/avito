<?php

namespace BaksDev\Avito\UseCase\Admin\Delete\Modify;

use BaksDev\Avito\Entity\Modifier\AvitoTokenModifierInterface;
use BaksDev\Core\Type\Modify\Modify\ModifyActionDelete;
use BaksDev\Core\Type\Modify\ModifyAction;

final class ModifyDTO implements AvitoTokenModifierInterface
{
    private readonly ModifyAction $action;

    public function __construct()
    {
        $this->action = new ModifyAction(ModifyActionDelete::class);
    }

    public function getAction(): ModifyAction
    {
        return $this->action;
    }
}
