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

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PersonLikeProductModuleController extends AbstractController
{
    private $session;
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function person_like_product(Request $request) : Response
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
                    ->add('state', ChoiceType::class, 
                                    ['label'=>'Choose the State:',
                                     'choices'=> [
                                        'Active' => Person::ACTIVE,
                                        'Banned' => Person::BANNED,
                                        'Deleted' => Person::DELETED,
                                        ],
                                    'placeholder'=>""
                                    ])  
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
        $productHavePersons = array();
        $personHaveProducts = array();
        
        if ($form_person->isSubmitted() ) {
                       
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
            }  
            $products = $queryBuilder->getQuery()->getResult();


            $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('p')
                                            -> from ('App\Entity\Person', 'p');
            if (isset($i_name)) {
                $queryBuilder=$queryBuilder->setParameter('i_name', strtolower($i_name))
                                            -> andwhere ($queryBuilder->expr()->eq(
                                                        $queryBuilder-> expr()->lower('p.i_name'), ':i_name') ) ;
            }
            if (isset($f_name)) {
                $queryBuilder=$queryBuilder->setParameter('f_name', strtolower($f_name))
                                            -> andwhere ( $queryBuilder->expr()->eq(
                                                          $queryBuilder-> expr()->lower('p.f_name'), ':f_name') ) ;
            }
            if (isset($login)) {
                $queryBuilder= $queryBuilder->setParameter('login', $login)
                                        -> andWhere('p.login = :login');
            }
            if (isset($state)) {
                $queryBuilder= $queryBuilder->setParameter('state', $state)
                                            -> andWhere('p.state = :state');
                $state=$person->getStateString();
            }
            $persons = $queryBuilder->getQuery()->getResult();
                        
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
        else {
            $contents = $this->renderView('person_like_product/person_like_product.html.twig', [
                    
                'form_person' => $form_person->createView(),
                'form_product' => $form_product->createView(),
                'match'=>$match,
                'products' => $products,
                'persons' => $persons,
                
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

            $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('p')
                                            -> from ('App\Entity\Product', 'p');
            if (isset($date_from) ) {
                $queryBuilder=$queryBuilder->setParameter('date_from', $date_from)
                                            ->andwhere ('p.public_date >= :date_from');
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
            
            /*foreach ($persons as $person) {
                array_push($personHaveProducts, $person->getPersonHaveProducts() ); 
            }*/

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
                'personHaveProducts'=>$personHaveProducts,
                    
            ]);

            
        } 
                     
        return new Response ($contents);
        
    }

    public function person_like_product_edit(Request $request, $id_person) : Response
    {   
        $personManager = $this->getDoctrine()->getManager();
        $person = $personManager->getRepository(Person::class)->find($id_person);

        $personHaveProducts = $person->getPersonHaveProducts();
        
        $productsLiked = array();
        $i=0;
        foreach ($personHaveProducts as $personHaveProduct) {
            $productsLiked[$i]=$personHaveProduct->getProduct();
            $i=$i+1;
        }

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
                
        $request= Request::createFromGlobals();
        $requestForm=$request->query->get('form');
        $this->session->set('sessionForm', $requestForm  );
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

            
            $request= Request::createFromGlobals();
            $requestForm=$request->query->get('form');
            $this->session->set('sessionForm', $requestForm  );
            
            if (isset($date_from) ) $date_from->modify('+1 second');

            $contents = $this->renderView('person_like_product_edit/person_like_product_edit.html.twig', [
                
                'form_product' => $form_product->createView(),
                'products' => $products,
                'person' => $person,

                'date_from' => $date_from,
                'date_to' => $date_to,
                'name' => $name,
                
                'productsLiked' => $productsLiked,
                    
            ]);

            
        } 
        else {
            $contents = $this->renderView('person_like_product_edit/person_like_product_edit.html.twig', [
                    
                'form_product' => $form_product->createView(),
                'products' => $products,
                'person' => $person, 
                'productsLiked' => $productsLiked,       
                    
            ]);
        }
               
        return new Response ($contents);
    }    

    public function person_like_product_add ($id_person, $id_product)
    {
        $requestForm=$this->session->get('sessionForm'); 
        
        $personLikeProductManager = $this->getDoctrine()->getManager();
        $personLikeProductArray = $personLikeProductManager->getRepository(PersonLikeProduct::class)
                                                        ->findBy ([
                                                            'person' => $id_person, 
                                                            'product' => $id_product 
                                                        ]);
        if (empty($personLikeProductArray) ) { 
            
            $productManager = $this->getDoctrine()->getManager();
            $product = $productManager->getRepository(Product::class)->find($id_product);

            $personManager = $this->getDoctrine()->getManager();
            $person = $personManager->getRepository(Person::class)->find($id_person);
            
            $personLikeProduct = new PersonLikeProduct();
            $personLikeProduct->setPerson($person);
            $personLikeProduct->setProduct($product);
            
            $personLikeProductManager ->persist($personLikeProduct);
            $personLikeProductManager ->flush();

            $request= Request::createFromGlobals();
            $requestForm=$this->session->get('sessionForm'); 
        }
       
        return $this->redirectToRoute( 'person_like_product_edit', ['id_person'=> $id_person,
                                                                    'form'=>$requestForm]);
    }

    public function person_like_product_delete ( $id_person, $id_product)
    {
        $personLikeProductManager = $this->getDoctrine()->getManager();
        $personLikeProductArray = $this->getDoctrine()->getRepository(PersonLikeProduct::class)
                                                ->findBy ([
                                                    'person' => $id_person, 
                                                    'product' => $id_product 
                                                ]);
            
        foreach ($personLikeProductArray as $persprod) {
            $personLikeProductManager->remove($persprod);
            $personLikeProductManager->flush();
        }   

        $requestForm=$this->session->get('sessionForm'); 
        
        return $this->redirectToRoute( 'person_like_product_edit', ['id_person'=> $id_person,
                                                                    'form'=>$requestForm]);
    }

    public function product_like_person_edit(Request $request, $id_product) : Response
    {   
        $productManager = $this->getDoctrine()->getManager();
        $product = $productManager->getRepository(Product::class)->find($id_product);

        $productHavePersons = $product->getProductHavePersons();
        
        $personsLoved = array();
        $i=0;
        foreach ($productHavePersons as $productHavePerson) {
            $personsLoved[$i]=$productHavePerson->getPerson();
            $i=$i+1;
        }

        $form_person = $this->createFormBuilder()
                                ->setMethod('GET')
                                ->add('login', EntityType::class, [
                                    'label'=>'Login (ATTENTION ON REGISTER!):',
                                    'class'=> Person::class,
                                    'choice_label' => 'login',
                                    'required' => false])
                                ->add('i_name', EntityType::class, [
                                    'label'=>'Name:',
                                    'class'=> Person::class,
                                    'choice_label' => 'i_name',
                                    'required' => false])
                                ->add('f_name', EntityType::class, [
                                    'label'=>'Surname:',
                                    'class'=> Person::class,
                                    'choice_label' => 'f_name',
                                    'required' => false])
                                ->add('state', ChoiceType::class, ['label'=>'User\'s state',
                                                            'placeholder'=>"",
                                                            'required' => false,
                                                            'choices'=> [
                                                                'Active' => Person::ACTIVE,
                                                                'Banned' => Person::BANNED,
                                                                'Deleted' => Person::DELETED,
                                                                ],
                                                            ])
                                ->add('send', SubmitType::class, ['label'=>'Show users'])
                                ->getForm();

        $form_person->handleRequest($request);
                
        $request= Request::createFromGlobals();
        $requestForm=$request->query->get('form');
        $this->session->set('sessionFormPerson', $requestForm  );
        $persons=array();
        
        if ($form_person->isSubmitted() ) {
                           
            $data = $form_person->getData();
            $login=$data['login'];
            $i_name=$data['i_name'];
            $f_name=$data['f_name'];
            $state=$data['state'];

            $entityManager = $this->getDoctrine()->getManager();
            $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('p')
                                            -> from ('App\Entity\Person', 'p')
                                            -> orderBy('p.state', 'ASC');

            if (isset($i_name)) {
                $i_name=$i_name->getIName();
                $queryBuilder=$queryBuilder->setParameter('i_name', strtolower($i_name))
                                            -> andwhere ($queryBuilder->expr()->eq(
                                                        $queryBuilder-> expr()->lower('p.i_name'), ':i_name') ) ;
            }

            if (isset($f_name)) {
                $f_name=$f_name->getFName();
                $queryBuilder=$queryBuilder->setParameter('f_name', strtolower($f_name))
                                            -> andwhere ( $queryBuilder->expr()->eq(
                                                          $queryBuilder-> expr()->lower('p.f_name'), ':f_name') ) ;
            }

            if (isset($login)) {
                $login=$login->getLogin();
                $queryBuilder= $queryBuilder->setParameter('login', $login)
                                        -> andWhere('p.login = :login');
            }
        
            if (isset($state)) {
                $queryBuilder= $queryBuilder->setParameter('state', $state)
                                            -> andWhere('p.state = :state');
            }

            $persons = $queryBuilder->getQuery()->getResult();

            
            $request= Request::createFromGlobals();
            $requestForm=$request->query->get('form');
            $this->session->set('sessionFormPerson', $requestForm  );
            
            $contents = $this->renderView('product_like_person_edit/product_like_person_edit.html.twig', [
                
                'form_person' => $form_person->createView(),
                'persons' => $persons,
                'product' => $product,

                'login'=> $login,
                'i_name'=> $i_name,
                'f_name'=> $f_name,
                'state'=> $state,
                'active' => Person::ACTIVE,
                'banned' => Person::BANNED,
                'deleted' => Person::DELETED,
                
                'personsLoved' => $personsLoved,
                    
            ]);

            
        } 
        else {
            $contents = $this->renderView('product_like_person_edit/product_like_person_edit.html.twig', [
                    
                'form_person' => $form_person->createView(),
                'persons' => $persons,
                'product' => $product, 
                'personsLoved' => $personsLoved,   
                    
            ]);
        }
               
        return new Response ($contents);
    }    

    public function product_like_person_add ($id_product, $id_person)
    {
        $requestForm=$this->session->get('sessionFormPerson'); 
        
        $productLikePersonManager = $this->getDoctrine()->getManager();
        $productLikePersonArray = $productLikePersonManager->getRepository(PersonLikeProduct::class)
                                                        ->findBy ([
                                                            'person' => $id_person, 
                                                            'product' => $id_product 
                                                        ]);
        if (empty($productLikePersonArray) ) { 
            
            $productManager = $this->getDoctrine()->getManager();
            $product = $productManager->getRepository(Product::class)->find($id_product);

            $personManager = $this->getDoctrine()->getManager();
            $person = $personManager->getRepository(Person::class)->find($id_person);
            
            $personLikeProduct = new PersonLikeProduct();
            $personLikeProduct->setPerson($person);
            $personLikeProduct->setProduct($product);
            
            $productLikePersonManager ->persist($personLikeProduct);
            $productLikePersonManager ->flush();

            $request= Request::createFromGlobals();
            $requestForm=$this->session->get('sessionFormPerson'); 
        }
        
        return $this->redirectToRoute( 'product_like_person_edit', ['id_product'=> $id_product,
                                                                    'form'=>$requestForm]);
    }

    public function product_like_person_delete ($id_product, $id_person)
    {
        $productLikePersonManager = $this->getDoctrine()->getManager();
        $productLikePersonArray = $this->getDoctrine()->getRepository(PersonLikeProduct::class)
                                                ->findBy ([
                                                    'person' => $id_person, 
                                                    'product' => $id_product 
                                                ]);
            
        foreach ($productLikePersonArray as $prodpers) {
            $productLikePersonManager->remove($prodpers);
            $productLikePersonManager->flush();
        }   

        $requestForm=$this->session->get('sessionFormPerson'); 
        
        return $this->redirectToRoute( 'product_like_person_edit', ['id_product'=> $id_product,
                                                                    'form'=>$requestForm]);
    }

}