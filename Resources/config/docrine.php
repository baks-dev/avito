<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Avito\BaksDevAvitoBundle;
use BaksDev\Avito\Type\Event\AvitoTokenEventType;
use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrine) {

    $doctrine->dbal()->type(AvitoTokenEventUid::TYPE)->class(AvitoTokenEventType::class);

    $emDefault = $doctrine->orm()->entityManager('default')->autoMapping(true);

    $emDefault
        ->mapping('avito')
        ->type('attribute')
        ->dir(BaksDevAvitoBundle::PATH.'Entity')
        ->isBundle(false)
        ->prefix(BaksDevAvitoBundle::NAMESPACE.'\\Entity')
        ->alias('avito');
};
