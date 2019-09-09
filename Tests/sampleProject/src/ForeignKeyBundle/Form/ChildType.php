<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Form;

use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Entity\ChildEntity;
use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Entity\ParentEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChildType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('parent', EntityType::class, [
            'class' => ParentEntity::class,
            'choice_value' => function(ParentEntity $entity = null){
                return $entity ? $entity->getId() : '';
            }
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChildEntity::class,
            "csrf_protection" => false
        ]);
    }
}
