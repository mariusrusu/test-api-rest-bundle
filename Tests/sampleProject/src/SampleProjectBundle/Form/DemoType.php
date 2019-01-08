<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\Form;

use EveryCheck\TestApiRestBundle\Tests\sampleProject\Entity\Demo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('value');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Demo::class,
            "csrf_protection" => false
        ]);
    }
}
