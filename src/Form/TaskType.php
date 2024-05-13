<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->translator->trans('form.labels.task.name'),
                'attr' => [
                    'placeholder' => 'form.placeholders.task.name',
                ],
            ])
            ->add('active', CheckboxType::class, [
                'label' => false,
                'help' => $this->translator->trans('form.help.task.active'),
                'required' => false,
            ])
            ->add('cronExpression', TextType::class, [
                'help' => $this->translator->trans('form.placeholders.task.cron_help'),
                'label' => $this->translator->trans('form.labels.task.cron'),
                'attr' => [
                    'value' => '0 0 * * *',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
