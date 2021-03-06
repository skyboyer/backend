<?php

namespace App\Form\Type;
//namespace App\Entity;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Form;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
               
        $builder
                ->add('login', ChoiceType::class, [
                                                    'label'=>'Login:',
                                                    'required' => false,
                                                    'attr' => array('class'=>'js-select2-person-login'),
                                                    'mapped' => false  ])
                ->add('i_name', ChoiceType::class, [
                                                    'label'=>'Name:',
                                                    'required' => false,
                                                    'attr' => array('class'=>'js-select2-person-i'),
                                                    'mapped' => false  ])
                ->add('f_name', ChoiceType::class, [
                                                    'label'=>'Surname:',
                                                    'required' => false,
                                                    'attr' => array('class'=>'js-select2-person-f'),
                                                    'mapped' => false  ])
                ->add('state', ChoiceType::class, [
                                                    'label'=>'Choose the State:',
                                                    'choices'=> [
                                                        'Active' => Person::ACTIVE,
                                                        'Banned' => Person::BANNED,
                                                        'Deleted' => Person::DELETED,
                                                        ],
                                                    'placeholder'=>"",
                                                    'expanded'=>true, 'multiple'=>true,
                                                    'data' => [Person::ACTIVE],
                                                    'mapped' => false  ]);
        
        /*  ->add('login', TextType::class, ['label'=>'Login (ATTENTION ON REGISTER!):'])
            ->add('i_name', TextType::class, ['label'=>'Name:'])
            ->add('f_name', TextType::class, ['label'=>'Surname:'])
            
            ->add('state', ChoiceType::class, [
                                                'label'=>'Choose the State:',
                                                'choices'=> [
                                                    'Active' => Person::ACTIVE,
                                                    'Banned' => Person::BANNED,
                                                    'Deleted' => Person::DELETED,
                                                    ],
                                                'placeholder'=>"",
                                                
                                            ]); */

        $builder->addEventListener(
            
            FormEvents::PRE_SUBMIT,
                                    
            function (FormEvent $event) 
            {
                $data = $event->getData();
                $form = $event->getForm();
                
                $choice_login = [$data['login'] => $data['login'] ];
                $choice_i = [$data['i_name'] => $data['i_name'] ];
                $choice_f = [$data['f_name'] => $data['f_name'] ];
                
                if ($form->has('form_person_like_product')) {   
                    $form->add  ('login', ChoiceType::class,  [ 
                                                                'label'=>'Login:',
                                                                'required' => false,
                                                                'choices' => $choice_login,
                                                                'mapped' => false,
                                                                'attr' => array('class'=>'js-select2-person-login'),
                                ]);

                    $form->add  ('i_name', ChoiceType::class,  [ 
                                                                'label'=>'Name:',  
                                                                'required' => false,
                                                                'choices' => $choice_i,
                                                                'mapped' => false,
                                                                'attr' => array('class'=>'js-select2-person-i'),
                                ]);
                    $form->add  ('f_name', ChoiceType::class,  [ 
                                                                'label'=>'Surname:',  
                                                                'required' => false,
                                                                'choices' => $choice_f,
                                                                'mapped' => false,
                                                                'attr' => array('class'=>'js-select2-person-f'),
                                ]);
                }
            }
        );
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
