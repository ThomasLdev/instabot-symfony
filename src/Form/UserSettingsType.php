<?php

namespace App\Form;

use App\Entity\UserSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserSettingsType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) { }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('googleDriveFolderId', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans('form.placeholders.drive.folder_id'),
                ],
            ])
            ->add('instagramToken', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans('form.placeholders.instagram_token'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSettings::class,
        ]);
    }
}
