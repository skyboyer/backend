<?php

namespace App\Form\Type;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name', ChoiceType::class, [
                                            'label'=>'Name:',
                                            'required' => false,
                                            'mapped' => false,
                                            'attr' => array('class'=>'js-select2-product-name') ])
                ->add('date_from', DateType::class, ['label'=>'Publication date from:',
                                            'required' => false,
                                            'widget' => 'single_text',
                                            'html5' => false,
                                            'mapped' => false,
                                            'attr' => ['class' => 'js-datepicker', 'readonly'=>'readonly'] ])
                ->add('date_to', DateType::class, ['label'=>'Publication date to:',
                                            'required' => false,
                                            'widget' => 'single_text',
                                            'html5' => false,
                                            'mapped' => false,
                                            'attr' => ['class' => 'js-datepicker', 'readonly'=>'readonly'] ]);
        
            /*  ->add('name', TextType::class, ['label'=>'Name:'])
                ->add('info', TextareaType::class, ['label'=>'Product information:'])
                ->add('public_date', DateType::class, [
                                'label'=>'Date of publication'
                            ])  */

        $builder->addEventListener(
            
            FormEvents::PRE_SUBMIT,
                                                            
            function (FormEvent $event) 
            {
                $data = $event->getData();
                $form = $event->getForm();
                                            
                $choice_name = [$data['name'] => $data['name'] ];
                                                
                if ($form->has('form_product_like_person')) {   
                    $form->add  ('name', ChoiceType::class,  [ 
                                                    'label'=>'Name:',
                                                    'required' => false,
                                                    'choices' => $choice_name,
                                                    'mapped' => false,
                                                    'attr' => array('class'=>'js-select2-product-name'),
                                ]);
                            
                                            
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
