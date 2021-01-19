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


class ProductLikePersonEditController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
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

