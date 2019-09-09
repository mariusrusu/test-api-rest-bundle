<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Form;

use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Entity\ParentEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParentType extends AbstractType
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
            'data_class' => ParentEntity::class,
            "csrf_protection" => false
        ]);
    }
}
