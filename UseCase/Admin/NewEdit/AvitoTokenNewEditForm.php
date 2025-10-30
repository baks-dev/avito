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

namespace BaksDev\Avito\UseCase\Admin\NewEdit;

use BaksDev\Avito\UseCase\Admin\NewEdit\Active\AvitoTokenActiveForm;
use BaksDev\Avito\UseCase\Admin\NewEdit\Address\AvitoTokenAddressForm;
use BaksDev\Avito\UseCase\Admin\NewEdit\Client\AvitoTokenClientForm;
use BaksDev\Avito\UseCase\Admin\NewEdit\Kit\AvitoTokenKitDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Kit\AvitoTokenKitForm;
use BaksDev\Avito\UseCase\Admin\NewEdit\Manager\AvitoTokenManagerForm;
use BaksDev\Avito\UseCase\Admin\NewEdit\Percent\AvitoTokenPercentForm;
use BaksDev\Avito\UseCase\Admin\NewEdit\Phone\AvitoTokenPhoneForm;
use BaksDev\Avito\UseCase\Admin\NewEdit\Profile\AvitoTokenProfileForm;
use BaksDev\Avito\UseCase\Admin\NewEdit\Secret\AvitoTokenSecretForm;
use BaksDev\Avito\UseCase\Admin\NewEdit\User\AvitoTokenUserForm;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileChoice\UserProfileChoiceInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AvitoTokenNewEditForm extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('profile', AvitoTokenProfileForm::class, ['label' => false]);

        $builder->add('active', AvitoTokenActiveForm::class, ['label' => false]);

        $builder->add('client', AvitoTokenClientForm::class, ['label' => false]);

        $builder->add('manager', AvitoTokenManagerForm::class, ['label' => false]);

        $builder->add('percent', AvitoTokenPercentForm::class, ['label' => false]);

        $builder->add('phone', AvitoTokenPhoneForm::class, ['label' => false]);

        $builder->add('secret', AvitoTokenSecretForm::class, ['label' => false]);

        $builder->add('user', AvitoTokenUserForm::class, ['label' => false]);

        $builder->add('address', AvitoTokenAddressForm::class, ['label' => false]);

        $builder->add('avito_token', SubmitType::class, [
            'label' => 'Save',
            'label_html' => true,
            'attr' => ['class' => 'btn-primary'],
        ]);

        $builder->addEventListener(
            eventName: FormEvents::PRE_SET_DATA,
            listener: function(FormEvent $event) use ($options) {

                $form = $event->getForm();

                $avitoTokenNewEditDTO = $event->getData();

                if($avitoTokenNewEditDTO instanceof AvitoTokenNewEditDTO)
                {
                    if(true === $avitoTokenNewEditDTO->getKit()->isEmpty())
                    {
                        $avitoTokenNewEditDTO->addKit(new AvitoTokenKitDTO);
                    }
                }

                $form->add('kit', CollectionType::class, [
                    'entry_type' => AvitoTokenKitForm::class,
                    'entry_options' => [
                        'label' => false,
                    ],
                    'label' => false,
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => '__kit__',
                ]);

            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AvitoTokenNewEditDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}
