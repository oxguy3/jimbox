<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LetterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nameFirst', TextType::class, [
                'required' => false,
                'label'    => 'First Name',
            ])
            ->add('nameLast', TextType::class, [
                'required' => true,
                'label'    => 'Last Name',
            ])
            ->add('originYear', IntegerType::class, [
                'required' => false,
            ])
            ->add('rating', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    0 => '0 - None',
                    1 => '1 - Excellent',
                    2 => '2 - Fair',
                    3 => '3 - Poor',
                ],
            ])
            ->add('letterType', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    'letter'   => 'Letter',
                    'postcard' => 'Postcard',
                    'note'     => 'Note',
                    'other'    => 'Other',
                ],
            ])
            ->add('recipientCategory', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    'reader' => 'Reader',
                    'critic' => 'Critic',
                    'family' => 'Family',
                    'lover'  => 'Lover',
                    'other'  => 'Other',
                ],
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
            ])
//            ->add('save', SubmitType::class, [
//                'label' => 'Submit'
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Letter'
        ));
    }
}