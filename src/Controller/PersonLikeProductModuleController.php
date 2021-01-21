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
    //form for filtering persons to see their likes
        $person = new Person();
        $form_person = $this->createForm (PersonType::class, $person,['method' => 'GET'])
                    ->add('login', EntityType::class, [
                                    'label'=>'Login (ATTENTION ON REGISTER!):',
                                    'class'=> Person::class,
                                    'choice_label' => 'login',
                                    'required' => false,
                                    'mapped' => false,
                                    'attr' => array(
                                        'class'=>'js-select2'
                                    ), ])
                    ->add('i_name', EntityType::class, [
                                    'label'=>'Name:',
                                    'class'=> Person::class,
                                    'choice_label' => 'i_name',
                                    'required' => false,
                                    'mapped' => false,
                                    'attr' => array(
                                        'class'=>'js-select2'
                                    ), ])
                    ->add('f_name', EntityType::class, [
                                    'label'=>'Surname:',
                                    'class'=> Person::class,
                                    'choice_label' => 'f_name',
                                    'required' => false,
                                    'mapped' => false,
                                    'attr' => array(
                                        'class'=>'js-select2'
                                    ), ])
                    ->add('state', ChoiceType::class, 
                                    ['label'=>'Choose the State:',
                                     'choices'=> [
                                        'Active' => Person::ACTIVE,
                                        'Banned' => Person::BANNED,
                                        'Deleted' => Person::DELETED],
                                    'placeholder'=>"",
                                    'expanded'=>true, 'multiple'=>true,
                                    'data' => [Person::ACTIVE],
                                    'mapped' => false])  
                    ->add('send', SubmitType::class, ['label'=>'Show products, which these users like']);
                                    
     //form for filtering products to see its lovers   
        $form_product = $this->createFormBuilder()
                    ->setMethod('GET')
                    ->add('name', EntityType::class, [
                                    'label'=>'Name:',
                                    'class'=> Product::class,
                                    'choice_label' => 'name',
                                    'required' => false,
                                    'attr' => array(
                                        'class'=>'js-select2'
                                    ), ])
                    ->add('date_from', DateType::class, ['label'=>'Publication date from:',
                                        'required' => false,
                                        'widget' => 'single_text',
                                        'html5' => false,
                                        'attr' => ['class' => 'datepicker', 'readonly'=>'readonly'] ])
                    ->add('date_to', DateType::class, ['label'=>'Publication date to:',
                                        'required' => false,
                                        'widget' => 'single_text',
                                        'html5' => false,
                                        'attr' => ['class' => 'datepicker', 'readonly'=>'readonly'] ])
                    ->add('send', SubmitType::class, ['label'=>'Show users, who love these products'])
                    ->getForm();

        $form_person->handleRequest($request);      
        $form_product->handleRequest($request);
        
        $match=0; // shows, that no form is submitted
        $products=array();
        $persons=array();
                
        if ($form_person->isSubmitted() ) {
                       
            $login=$form_person->get('login')->getData();
            $i_name=$form_person->get('i_name')->getData();
            $f_name=$form_person->get('f_name')->getData();
            $states=$form_person->get('state')->getData();

        //filtering persons based on form info
            $entityManager = $this->getDoctrine()->getManager();
            $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('p')
                                            -> from ('App\Entity\Person', 'p');
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
            if (!empty($states)) {
                $queryBuilder= $queryBuilder->setParameter('states', $states)
                                            -> andWhere('p.state in (:states)');
            }
            $persons = $queryBuilder->getQuery()->getResult();
                        
            $match=1;  // shows, that form_person is submitted

        //array of chosen in form states in string format    
            $statesString=array();
            foreach($states as $state)
            {
                if ($state== Person::ACTIVE) array_push($statesString, 'ACTIVE');
                if ($state==Person::BANNED) array_push($statesString, 'BANNED');
                if ($state==Person::DELETED) array_push($statesString, 'DELETED');
            }
        
            $contents = $this->renderView('person_like_product/person_like_product.html.twig', [
                
                'form_person' => $form_person->createView(),
                'form_product' => $form_product->createView(),
                'match'=>$match,
                'products' => $products,
                'persons' => $persons,
                               
                'login' => $login,
                'i_name' => $i_name,
                'f_name' => $f_name,
                'states' => $statesString,
                                    
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

        //filtering products based on form info
            $entityManager = $this->getDoctrine()->getManager();
            $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('p')
                                            -> from ('App\Entity\Product', 'p');
            if (isset($date_from) ) {
                $date_from=date_format($date_from, 'Y-m-d');
                $queryBuilder=$queryBuilder->setParameter('date_from', $date_from)
                                            ->andwhere ('p.public_date >= :date_from');
            }
            if (isset($date_to) ) {
                $date_to=date_format($date_to, 'Y-m-d');
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
            
            $match=2;  // shows, that form_product is submitted
           
            $contents = $this->renderView('person_like_product/person_like_product.html.twig', [
                
                'form_person' => $form_person->createView(),
                'form_product' => $form_product->createView(),
                'match'=>$match,
                'products' => $products,
                'persons' => $persons,
                
                'date_from' => $date_from,
                'date_to' => $date_to,
                'name' => $name,
                                    
            ]);
   
        } 
        return new Response ($contents);
        
    }

    public function person_like_product_edit(Request $request, $id_person) : Response
    {   
    //editing info about person's likes    
        $personManager = $this->getDoctrine()->getManager();
        $person = $personManager->getRepository(Person::class)->find($id_person);

    //making array of liked Products objects    
        $personHaveProducts = $person->getPersonHaveProducts();
        $productsLiked = array();
        foreach ($personHaveProducts as $personHaveProduct) {
            array_push($productsLiked, $personHaveProduct->getProduct());
        }

    //form for chose products to like    
        $form_product = $this->createFormBuilder()
                    ->setMethod('GET')
                    ->add('name', EntityType::class, [
                                    'label'=>'Name:',
                                    'class'=> Product::class,
                                    'choice_label' => 'name',
                                    'required' => false,
                                    'attr' => array(
                                        'class'=>'js-select2'
                                    ), ])
                    ->add('date_from', DateType::class, ['label'=>'Publication date from:',
                                        'required' => false,
                                        'widget' => 'single_text',
                                        'html5' => false,
                                        'attr' => ['class' => 'datepicker', 'readonly'=>'readonly'] ])
                    ->add('date_to', DateType::class, ['label'=>'Publication date to:',
                                        'required' => false,
                                        'widget' => 'single_text',
                                        'html5' => false,
                                        'attr' => ['class' => 'datepicker', 'readonly'=>'readonly'] ])
                    ->add('send', SubmitType::class, ['label'=>'Show products'])
                    ->getForm();

        $form_product->handleRequest($request);
                
    //saving info about chosen/not chosen products (by saving GET form parameters) 
        $request= Request::createFromGlobals();
        $requestForm=$request->query->get('form');
        $this->session->set('sessionForm', $requestForm  );
        
        $products=array();

    //filtering products    
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
                $date_from=date_format($date_from, 'Y-m-d');
                $queryBuilder=$queryBuilder->setParameter('date_from', $date_from)
                                            ->andwhere ('p.public_date >= :date_from');
            }
                                                                           
            if (isset($date_to) ) {
                $date_to=date_format($date_to, 'Y-m-d');
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

        //saving info about chosen/not chosen products (by saving GET form parameters) 
            $request= Request::createFromGlobals();
            $requestForm=$request->query->get('form');
            $this->session->set('sessionForm', $requestForm  );
            
            $contents = $this->renderView('person_like_product_edit/person_like_product_edit.html.twig', [
                
                'form_product' => $form_product->createView(),
                'products' => $products,
                'person' => $person,

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
        
    // extracting array of PersonLikeProduct objects for the chosen product and person
        $personLikeProductManager = $this->getDoctrine()->getManager();
        $personLikeProductArray = $personLikeProductManager->getRepository(PersonLikeProduct::class)
                                                        ->findBy ([
                                                            'person' => $id_person, 
                                                            'product' => $id_product 
                                                        ]);
    //checking if this like is already exist for the person  and adding new like for the person if not exist
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

        //reconstraction of chosen/not chosen products (by getting saved GET form parameters) 
            $request= Request::createFromGlobals();
            $requestForm=$this->session->get('sessionForm'); 
        }
       
        return $this->redirectToRoute( 'person_like_product_edit', ['id_person'=> $id_person,
                                                                    'form'=>$requestForm]);
    }

    public function person_like_product_delete ( $id_person, $id_product)
    {
        
    // extracting array of PersonLikeProduct objects for the chosen product and person    
        $personLikeProductManager = $this->getDoctrine()->getManager();
        $personLikeProductArray = $this->getDoctrine()->getRepository(PersonLikeProduct::class)
                                                ->findBy ([
                                                    'person' => $id_person, 
                                                    'product' => $id_product 
                                                ]);
    // delete like for the person   
        foreach ($personLikeProductArray as $persprod) {
            $personLikeProductManager->remove($persprod);
            $personLikeProductManager->flush();
        }   
    //reconstraction of chosen/not chosen products (by getting saved GET form parameters) 
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
        foreach ($productHavePersons as $productHavePerson) {
            array_push($personsLoved, $productHavePerson->getPerson());
        }

        $form_person = $this->createFormBuilder()
                                ->setMethod('GET')
                                ->add('login', EntityType::class, [
                                    'label'=>'Login (ATTENTION ON REGISTER!):',
                                    'class'=> Person::class,
                                    'choice_label' => 'login',
                                    'required' => false,
                                    'attr' => array(
                                        'class'=>'js-select2'
                                    ), ])
                                ->add('i_name', EntityType::class, [
                                    'label'=>'Name:',
                                    'class'=> Person::class,
                                    'choice_label' => 'i_name',
                                    'required' => false,
                                    'attr' => array(
                                        'class'=>'js-select2'
                                    ), ])
                                ->add('f_name', EntityType::class, [
                                    'label'=>'Surname:',
                                    'class'=> Person::class,
                                    'choice_label' => 'f_name',
                                    'required' => false,
                                    'attr' => array(
                                        'class'=>'js-select2'
                                    ), ])
                                ->add('state', ChoiceType::class, ['label'=>'User\'s state:',
                                                            'placeholder'=>"",
                                                            //'required' => true,
                                                            'expanded'=>true, 'multiple'=>true,
                                                            'choices'=> [
                                                                'Active' => Person::ACTIVE,
                                                                'Banned' => Person::BANNED,
                                                                'Deleted' => Person::DELETED,
                                                                ],
                                                            'data' => [Person::ACTIVE],
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
                                            -> from ('App\Entity\Person', 'p');
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
            if (!empty($state)) {
                $queryBuilder= $queryBuilder->setParameter('state', $state)
                                            -> andWhere('p.state in (:state)');
            }
            $persons = $queryBuilder->getQuery()->getResult();

            $request= Request::createFromGlobals();
            $requestForm=$request->query->get('form');
            $this->session->set('sessionFormPerson', $requestForm  );
            
            $contents = $this->renderView('product_like_person_edit/product_like_person_edit.html.twig', [
                
                'form_person' => $form_person->createView(),
                'persons' => $persons,
                'product' => $product,
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
        
    // extracting array of PersonLikeProduct objects for the chosen product and person        
        $productLikePersonManager = $this->getDoctrine()->getManager();
        $productLikePersonArray = $productLikePersonManager->getRepository(PersonLikeProduct::class)
                                                        ->findBy ([
                                                            'person' => $id_person, 
                                                            'product' => $id_product 
                                                        ]);
    //checking if this lover is already exist for the person  and adding new like for the person if not exist
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

        //reconstraction of chosen/not chosen products (by getting saved GET form parameters) 
            $request= Request::createFromGlobals();
            $requestForm=$this->session->get('sessionFormPerson'); 
        }
        
        return $this->redirectToRoute( 'product_like_person_edit', ['id_product'=> $id_product,
                                                                    'form'=>$requestForm]);
    }

    public function product_like_person_delete ($id_product, $id_person)
    {
    // extracting array of PersonLikeProduct objects for the chosen product and person    
        $productLikePersonManager = $this->getDoctrine()->getManager();
        $productLikePersonArray = $this->getDoctrine()->getRepository(PersonLikeProduct::class)
                                                ->findBy ([
                                                    'person' => $id_person, 
                                                    'product' => $id_product 
                                                ]);
    //delete lover for the product    
        foreach ($productLikePersonArray as $prodpers) {
            $productLikePersonManager->remove($prodpers);
            $productLikePersonManager->flush();
        }   

    //reconstraction of chosen/not chosen products (by getting saved GET form parameters) 
        $requestForm=$this->session->get('sessionFormPerson'); 
        
        return $this->redirectToRoute( 'product_like_person_edit', ['id_product'=> $id_product,
                                                                    'form'=>$requestForm]);
    }

}
