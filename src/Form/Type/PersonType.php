<?php

namespace App\Form\Type;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', TextType::class, ['label'=>'Login:'])
            ->add('i_name', TextType::class, ['label'=>'Name:'])
            ->add('f_name', TextType::class, ['label'=>'Surname:'])
            ->add('state', ChoiceType::class, 
                            ['label'=>'Choose the State:',
                            'choices'=> [
                                'Active' => 1,
                                'Banned' => 2,
                                'Deleted' => 3,
                                ],
                            ]);  

                        
   
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
