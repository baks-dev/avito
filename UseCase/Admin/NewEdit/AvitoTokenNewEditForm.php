<?php

declare(strict_types=1);

namespace BaksDev\Avito\UseCase\Admin\NewEdit;

use BaksDev\Avito\UseCase\Admin\NewEdit\Profile\AvitoTokenProfileForm;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileChoice\UserProfileChoiceInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AvitoTokenNewEditForm extends AbstractType
{
    public function __construct(
        private readonly UserProfileChoiceInterface $profileChoice,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var AvitoTokenNewEditDTO $data */
        $data = $builder->getData();

        if (null === $data->getProfile())
        {
            $builder->add('profile', ChoiceType::class, [
                'choices' => $this->profileChoice->getActiveUserProfile(),
                'choice_value' => function (?UserProfileUid $profile) {
                    return $profile?->getValue();
                },
                'choice_label' => function (UserProfileUid $profile) {
                    return $profile->getAttr();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'attr' => ['data-select' => 'select2'],
            ]);
        }

        $builder->add('client', TextType::class);

        $builder->add('secret', TextType::class, [
            'required' => false,
        ]);

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN') || $this->authorizationChecker->isGranted('ROLE_AVITO_TOKEN_ACTIVE'))
        {
            $builder->add('active', CheckboxType::class, ['required' => false]);
        }

        $builder->add('tokenProfile', AvitoTokenProfileForm::class);

        $builder->add('avito_token', SubmitType::class, [
            'label' => 'Save',
            'label_html' => true,
            'attr' => ['class' => 'btn-primary'],
        ]);
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
