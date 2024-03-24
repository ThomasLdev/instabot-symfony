<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Form;

use App\EventListener\Form\ReCaptchaValidationListener;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReCaptchaType extends AbstractType
{
    public function __construct(
        private readonly ReCaptcha $reCaptcha
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new ReCaptchaValidationListener($this->reCaptcha));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['type'] = $options['type'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('type', 'invisible')
            ->setAllowedValues('type', ['checkbox', 'invisible']);
    }
}
