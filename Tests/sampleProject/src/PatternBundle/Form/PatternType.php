<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\PatternBundle\Form;

use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\PatternBundle\Entity\Pattern;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PatternType extends AbstractType
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
            'data_class' => Pattern::class,
            "csrf_protection" => false
        ]);
    }
}
