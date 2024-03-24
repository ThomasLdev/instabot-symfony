<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'form.placeholders.email',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'form.validation.email.required',
                    ]),
                    new Email([
                        'message' => 'form.validation.email.invalid',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'form.placeholders.password',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'form.validation.password.required',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'form.validation.password.invalid',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('captcha', ReCaptchaType::class, [
                'mapped' => false,
                'type' => 'invisible',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
