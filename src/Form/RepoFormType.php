<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', TextType::class, array('label'=>'Repo Name',
            'attr' => array('placeholder' => 'Repository name','class'=>"mdc-text-field mdc-text-field--box mdc-text-field--with-leading-icon w-80", 'required'=>'required','trim' => true),
        ))
        ->add('description', TextType::class, array('label'=>'Repo Description',
            'attr' => array('placeholder' => 'Description','class'=>"mdc-text-field mdc-text-field--box mdc-text-field--with-leading-icon w-80"),
        ))
        ->add('visibility', ChoiceType::class, array(
            'expanded' => true,
            'choices' => array(
                'Public repository' => false,
                'Private repository' => true,
            )
        ))
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Repo'
        ]);
    }
}
