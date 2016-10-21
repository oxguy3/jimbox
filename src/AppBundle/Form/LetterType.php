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
                'label'    => 'First name',
            ])
            ->add('nameLast', TextType::class, [
                'required' => true,
                'label'    => 'Last name',
            ])
            ->add('originYear', IntegerType::class, [
                'required' => false,
                'label'    => 'Year',
            ])
            ->add('originMonth', IntegerType::class, [
                'required' => false,
                'label'    => 'Month',
            ])
            ->add('rating', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    0 => '0 - None',
                    1 => '1 - No',
                    2 => '2 - Maybe',
                    3 => '3 - Yes',
                ],
            ])
            ->add('letterType', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    'letter'   => 'letter',
                    'postcard' => 'postcard',
                    'note'     => 'note',
                    'other'    => 'other',
                ],
            ])
            ->add('recipientCategory', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    'reader' => 'reader',
                    'critic' => 'critic',
                    'family' => 'family',
                    'lover'  => 'lover',
                    'mother' => 'mother',
                    'child'  => 'child',
                    'spouse' => 'spouse',
                    'other'  => 'other',
                ],
                'label'    => 'Category',
            ])
            ->add('home', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    'Houghton'     => 'Houghton',
                    'Ransom'       => 'Ransom',
                    'Schlesinger'  => 'Schlesinger',
                    'NY Public'    => 'NY Public',
                    'Illinois'     => 'Illinois',
                    'Personal'     => 'Personal',
                    'Other'        => 'Other',
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