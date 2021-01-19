<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\AbstractType;

use App\Entity\Product;
use App\Form\Type\ProductType;
use App\Repository\ProductRepository;

use App\Entity\Person;
use App\Form\Type\PersonType;
use App\Repository\PersonRepository;

use App\Entity\PersonLikeProduct;
use App\Form\Type\PersonLikeProductType;
use App\Repository\PersonLikeProductRepository;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;


class PersonLikeProductEditController extends AbstractController
{
    public function person_like_product_edit(Request $request, $id_person) : Response
    {   
        $personManager = $this->getDoctrine()->getManager();
        $person = $personManager->getRepository(Person::class)->find($id_person);

        $personHaveProducts = $person->getPersonHaveProducts();

        $form_product = $this->createFormBuilder()
                    ->setMethod('GET')
                    ->add('name', EntityType::class, [
                                    'label'=>'Name:',
                                    'class'=> Product::class,
                                    'choice_label' => 'name',
                                    'required' => false ])
                    ->add('date_from', DateType::class, ['label'=>'Publication date from:',
                                        'required' => false,
                                        'widget' => 'single_text'])
                    ->add('date_to', DateType::class, ['label'=>'Publication date to:',
                                        'required' => false,
                                        'widget' => 'single_text'])
                    ->add('send', SubmitType::class, ['label'=>'Show products'])
                    ->getForm();

        $form_product->handleRequest($request);
        
        
        $products=array();
        if ($form_product->isSubmitted() ) {
            
            $data = $form_product->getData();
            $name=$data['name'];
            $date_from=$data['date_from'];
            $date_to=$data['date_to'];

            $entityManager = $this->getDoctrine()->getManager();
            $queryBuilder = $entityManager->createQueryBuilder()
                                        -> select('p')
                                        -> from ('App\Entity\Product', 'p')
                                        -> orderBy('p.public_date', 'ASC');
                                            

            if (isset($date_from) ) {
               
                $date_from->modify('-1 second');
                $queryBuilder=$queryBuilder->setParameter('date_from', $date_from)
                                            ->andwhere ('p.public_date >= :date_from');
                                   
            }
                                                                           
            if (isset($date_to) ) {
                $queryBuilder=$queryBuilder->setParameter('date_to', $date_to)
                                            ->andwhere ('p.public_date<= :date_to');
            }  

            if (isset($name)) {
                $name=$name->getName();
                $queryBuilder=$queryBuilder->setParameter('name', strtolower($name))
                                            ->andwhere ($queryBuilder->expr()->eq(
                                                       $queryBuilder-> expr()->lower('p.name'), ':name') ) ;
            }

            $products = $queryBuilder->getQuery()->getResult();
            
            
            if (isset($date_from) ) $date_from->modify('+1 second');

            $contents = $this->renderView('person_like_product_edit/person_like_product_edit.html.twig', [
                
                'form_product' => $form_product->createView(),
                'products' => $products,
                'person' => $person,

                'date_from' => $date_from,
                'date_to' => $date_to,
                'name' => $name,
                               
                'persontHaveProducts'=>$personHaveProducts,
                    
            ]);

            
        } 
        else {
            $contents = $this->renderView('person_like_product_edit/person_like_product_edit.html.twig', [
                    
                'form_product' => $form_product->createView(),
                'products' => $products,
                'person' => $person,          
                    
            ]);
        }
               
        return new Response ($contents);
    }    

    public function person_like_product_add ($id_person, $id_product)
    {
        $personLikeProductManager = $this->getDoctrine()->getManager();
        $personLikeProduct = $productManager->getRepository(PersonLikeProduct::class)
                                                ->findBy ([
                                                    'id_person' => $id_person, 
                                                    'id_product' => $id_product 
                                                ]);
            
        $productManager->remove($personLikeProduct);
        $productManager->flush();
    
        return $this->redirectToRoute('person_like_product_edit');
    }

    public function person_like_product_delete ($id_person, $id_product)
    {
        $personLikeProductManager = $this->getDoctrine()->getManager();
        $personLikeProduct = $this->getDoctrine()->getRepository(PersonLikeProduct::class)
                                                ->findBy ([
                                                    'person' => $id_person, 
                                                    'product' => $id_product 
                                                ]);
            
        $personLikeProductManager->remove($personLikeProduct[0]);
        $personLikeProductManager->flush();
    
        return $this->redirectToRoute( 'person_like_product_edit', array ('id_person'=> $id_person));
    }
        
        
}

