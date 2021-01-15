<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\AbstractType;


use App\Entity\Product;
use App\Form\Type\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;


class PersonController extends AbstractController
{
    public function person(Request $request) : Response
    {   
        
        $form = $this->createFormBuilder()
                    ->setMethod('GET')
                    ->add('name', TextType::class, ['label'=>'Name:',
                                        'required' => false])
                    ->add('date_from', DateType::class, ['label'=>'Date from:',
                                                'required' => false,
                                                'widget' => 'single_text'])
                    ->add('date_to', DateType::class, ['label'=>'Date to:',
                                                'required' => false,
                                                'widget' => 'single_text'])
                    ->add('send', SubmitType::class, ['label'=>'Show the chosen products'])
                    ->getForm();

        $form->handleRequest($request);

        function products_dates_to_string ($products) {
            $i=0;
            foreach ($products as $product) {
                
                $date_object = $product->getPublicDate();
                $date_string = date_format($date_object, 'Y-m-d');
                $products[$i]->publicdate = $date_string;
                $i=$i+1;

            }
            return ($products);
        };

        if ($form->isSubmitted() ) {
            
            $data = $form->getData();
            $date_from=$data['date_from'];
            $date_to=$data['date_to'];
            $name=$data['name'];

            $entityManager = $this->getDoctrine()->getManager();
            $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('p')
                                            -> from ('App\Entity\Product', 'p')
                                            -> orderBy('p.public_date', 'ASC');

            if (isset($name)) {
                $queryBuilder= $queryBuilder->setParameter('name', $name)
                                            -> andWhere('p.name = :name');
            }

            $products = $queryBuilder->getQuery()->getResult();

            $productsFilterByDate = Array();
            $i=0;
            foreach ($products as $product) {
                if (!isset($date_to)) {  
                    if ( ($product->getPublicDate() >= $date_from) ) {
                        $productsFilterByDate[$i]=$product;
                        $i=$i+1;
                    }   
                }    
                else {
                    if ( ($product->getPublicDate() >= $date_from) and ($product->getPublicDate() <= $date_to) ) {
                                    
                        $productsFilterByDate[$i]=$product;
                        $i=$i+1;
                    }
                }
            }
            $products=$productsFilterByDate;  
            
            $products=products_dates_to_string ($products);

            $contents = $this->renderView('product/product.html.twig',
                [
                    'form' => $form->createView(),
                    'products' => $products,
                ],
            );               
        }
        else {

           $products = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();
                        
            $products=products_dates_to_string ($products);

            $contents = $this->renderView('product/product.html.twig', [
                
                'form' => $form->createView(),
                'products' => $products,
                
            ]);
        }
            return new Response ($contents);
        
    }
}
