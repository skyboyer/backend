<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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


class ProductController extends AbstractController
{
    public function product(Request $request) : Response
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
                                                'widget' => 'single_text',
                                                'html5' => false,
                                                'attr' => ['class' => 'js-datepicker']
                                                
                                                
                                                
                                                ])
                    ->add('send', SubmitType::class, ['label'=>'Show the chosen products'])
                    ->getForm();

        $form->handleRequest($request);

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
                                            

            if (isset($date_from) ) {
                $date_from->modify('-1 second');
                $queryBuilder=$queryBuilder->setParameter('date_from', $date_from)
                                            ->andwhere ('p.public_date > :date_from');
            }
                                           
            if (isset($date_to) ) {
                $queryBuilder=$queryBuilder->setParameter('date_to', $date_to)
                                            ->andwhere ('p.public_date<= :date_to');
            }
            
            if (isset($name)) {
                $queryBuilder=$queryBuilder->setParameter('name', strtolower($name))
                                            ->andwhere ($queryBuilder->expr()->eq(
                                                       $queryBuilder-> expr()->lower('p.name'), ':name') ) ;
            }
   
            $products = $queryBuilder->getQuery()->getResult();
 
        }

        else {

           $products = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();
        }                
        
        $contents = $this->renderView('product/product.html.twig', [
                
            'form' => $form->createView(),
            'products' => $products,
                
            ]);
        return new Response ($contents);
        
    }
}
