<?php

declare(strict_types=1);

namespace BaksDev\Avito\UseCase\Admin\NewEdit\Profile;

use BaksDev\Field\Pack\Phone\Choice\PhoneFieldChoice;
use BaksDev\Field\Pack\Phone\Form\PhoneFieldForm;
use BaksDev\Field\Pack\Phone\Type\PhoneField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AvitoTokenProfileForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('address', TextType::class);

        $builder->add('manager', TextType::class);

        $builder->add('phone', PhoneFieldForm::class);

        $builder->add('percent', IntegerType::class, [
            'attr' => ['max' => 100, 'min' => 0]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AvitoTokenProfileDTO::class,
        ]);
    }
}
