<?php

namespace Mikamatto\Scheduler\Form;

use Mikamatto\Scheduler\Entity\SchedulerTask;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchedulerTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('command', TextType::class, [
                'help' => 'The command to run',
            ])
            ->add('description', TextType::class, [
                'required' => true,
                'help' => 'Short description of the task',
            ])
            ->add('cycle', IntegerType::class, [
                'help' => 'Interval in minutes between each run',
            ])
            ->add('ts_nextRun', TimeType::class, [
                'label' => 'Start Time',
                'help' => 'Time of first execution - Server time is currently: ' . date('H:i'),
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SchedulerTask::class,
        ]);
    }
}
