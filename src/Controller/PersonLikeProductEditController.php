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
    public function person_like_product_edit(Request $request, $id, $match) : Response
    {   
        $person = new Person();
        $form_person = $this->createForm (PersonType::class, $person,['method' => 'GET'])
                    ->add('login', EntityType::class, [
                                    'label'=>'Login (ATTENTION ON REGISTER!):',
                                    'class'=> Person::class,
                                    'choice_label' => 'login',
                                    'required' => false,
                                    'mapped' => false])
                    ->add('i_name', EntityType::class, [
                                    'label'=>'Name:',
                                    'class'=> Person::class,
                                    'choice_label' => 'i_name',
                                    'required' => false,
                                    'mapped' => false])
                    ->add('f_name', EntityType::class, [
                                    'label'=>'Surname:',
                                    'class'=> Person::class,
                                    'choice_label' => 'f_name',
                                    'required' => false,
                                    'mapped' => false])
                    ->add('send', SubmitType::class, ['label'=>'Show products, which these users like']);
                    

                                      
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
                    ->add('send', SubmitType::class, ['label'=>'Show users, who love these products'])
                    ->getForm();

        $form_person->handleRequest($request);      
        $form_product->handleRequest($request);
        
        $match=0;
        $products=array();
        $persons=array();
        
        if ($form_person->isSubmitted() ) {
            $personManager = $this->getDoctrine()->getManager(); //проверить надо ли эта часть!!
            $personManager->persist ($person);
           
            $login=$form_person->get('login')->getData();
            $i_name=$form_person->get('i_name')->getData();
            $f_name=$form_person->get('f_name')->getData();
            $state=$person->getState();

            $entityManager = $this->getDoctrine()->getManager();
            $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('prod', 'prodpers', 'pers')
                                            -> from ('App\Entity\Product', 'prod')
                                            -> join ('prod.ProductHavePersons', 'prodpers')
                                            -> join ('prodpers.person', 'pers');
                                            //-> orderBy('p.state', 'ASC');

            if (isset($i_name)) {
                $i_name=$i_name->getIName();
                $queryBuilder=$queryBuilder->setParameter('i_name', strtolower($i_name))
                                            -> andwhere ($queryBuilder->expr()->eq(
                                                        $queryBuilder-> expr()->lower('pers.i_name'), ':i_name') ) ;
            }

            if (isset($f_name)) {
                $f_name=$f_name->getFName();
                $queryBuilder=$queryBuilder->setParameter('f_name', strtolower($f_name))
                                            -> andwhere ( $queryBuilder->expr()->eq(
                                                          $queryBuilder-> expr()->lower('pers.f_name'), ':f_name') ) ;
            }  

            if (isset($login)) {
                $login=$login->getLogin();
                $queryBuilder= $queryBuilder->setParameter('login', $login)
                                        -> andWhere('pers.login = :login');
            }
        
            if (isset($state)) {
                $queryBuilder= $queryBuilder->setParameter('state', $state)
                                            -> andWhere('pers.state = :state');
                $state=$person->getStateString();
            }  

            $products = $queryBuilder->getQuery()->getResult();
            
            $productHavePersons = array();
            foreach ($products as $product) {
                array_push($productHavePersons, $product->getProductHavePersons() ); 
            }
            $match=1;

            $contents = $this->renderView('person_like_product/person_like_product.html.twig', [
                
                'form_person' => $form_person->createView(),
                'form_product' => $form_product->createView(),
                'match'=>$match,
                'products' => $products,
                'persons' => $persons,
                               
                'login' => $login,
                'i_name' => $i_name,
                'f_name' => $f_name,
                'state' => $state,
    
                'productHavePersons'=>$productHavePersons
                    
            ]);

                                      
        }
        
        if ($form_product->isSubmitted() ) {
            
            $data = $form_product->getData();
            $name=$data['name'];
            $date_from=$data['date_from'];
            $date_to=$data['date_to'];

            $entityManager = $this->getDoctrine()->getManager();
            $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('pers', 'persprod', 'prod')
                                            -> from ('App\Entity\Person', 'pers')
                                            -> join ('pers.PersonHaveProducts', 'persprod')
                                            -> join ('persprod.product', 'prod');
                                            

            if (isset($date_from) ) {
               
                $date_from->modify('-1 second');
                $queryBuilder=$queryBuilder->setParameter('date_from', $date_from)
                                            ->andwhere ('prod.public_date >= :date_from');
                                   
            }
                                                                           
            if (isset($date_to) ) {
                $queryBuilder=$queryBuilder->setParameter('date_to', $date_to)
                                            ->andwhere ('prod.public_date<= :date_to');
            }  

            if (isset($name)) {
                $name=$name->getName();
                $queryBuilder=$queryBuilder->setParameter('name', strtolower($name))
                                            ->andwhere ($queryBuilder->expr()->eq(
                                                       $queryBuilder-> expr()->lower('prod.name'), ':name') ) ;
            }

            $persons = $queryBuilder->getQuery()->getResult();

            $personHaveProducts = array();
            foreach ($persons as $person) {
                array_push($personHaveProducts, $person->getPersonHaveProducts() ); 
            }
            $match=2;
            if (isset($date_from) ) $date_from->modify('+1 second');

            $contents = $this->renderView('person_like_product/person_like_product.html.twig', [
                
                'form_person' => $form_person->createView(),
                'form_product' => $form_product->createView(),
                'match'=>$match,
                'products' => $products,
                'persons' => $persons,
                'date_from' => $date_from,
                'date_to' => $date_to,

                'name' => $name,
                               
                'persontHaveProducts'=>$personHaveProducts,
                    
            ]);

            
        } 
        else {
            $contents = $this->renderView('person_like_product/person_like_product.html.twig', [
                    
                'form_person' => $form_person->createView(),
                'form_product' => $form_product->createView(),
                'match'=>$match,
                'products' => $products,
                'persons' => $persons,            
                    
            ]);
        }
               
        return new Response ($contents);
        
    }
}
