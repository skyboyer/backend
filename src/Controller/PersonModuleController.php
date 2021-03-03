<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\AbstractType;

use App\Entity\Person;
use App\Form\Type\PersonType;
use App\Repository\PersonRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;


class PersonModuleController extends AbstractController
{
    public function person(Request $request) : Response
    {   
    // filter for persons
        $person = new Person();
        $form_person = $this->createForm (PersonType::class, $person,['method' => 'GET'])
                                        ->add('login', TextType::class, ['label'=>'Login:',
                                                                        'required' => false])
                                        ->add('i_name', TextType::class, ['label'=>'Name:',
                                                                        'required' => false])
                                        ->add('f_name', TextType::class, ['label'=>'Surname:',
                                                                        'required' => false])
                                        ->add('send', SubmitType::class, ['label'=>'Show the chosen users']);
                    
        $form_person->handleRequest($request);

        if ($form_person->isSubmitted() ) {
            
            $login=$form_person->get('login')->getData();
            $i_name=$form_person->get('i_name')->getData();
            $f_name=$form_person->get('f_name')->getData();  
            $states=$form_person->get('state')->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('p')
                                            -> from ('App\Entity\Person', 'p')
                                            -> orderBy('p.state', 'ASC');
            if (isset($i_name)) {
                $queryBuilder=$queryBuilder -> setParameter('i_name', addcslashes($i_name, '%_').'%') 
                                            -> andwhere ('p.i_name LIKE :i_name') ;
                                            
                                            /*  ->setParameter('i_name', strtolower($i_name))
                                                -> andwhere ($queryBuilder->expr()->eq(
                                                            $queryBuilder-> expr()->lower('p.i_name'), ':i_name') ) ;*/
            }
            if (isset($f_name)) {
                $queryBuilder=$queryBuilder ->setParameter('f_name', addcslashes($f_name, '%_').'%') 
                                            -> andwhere ('p.f_name LIKE :f_name') ;
            }
            if (isset($login)) {
                $queryBuilder= $queryBuilder->setParameter('login', addcslashes($login, '%_').'%') 
                                            -> andWhere('p.login LIKE :login');
            }
            if (!empty($states)) {
            $queryBuilder= $queryBuilder->setParameter('states', $states)
                                            -> andWhere('p.state in (:states)');
            }

            $persons = $queryBuilder->getQuery()->getResult();
                            
        }
        else {

            $entityManager = $this->getDoctrine()->getManager();
            $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('p')
                                            -> from ('App\Entity\Person', 'p')
                                            -> orderBy('p.state', 'ASC');
            $persons = $queryBuilder->getQuery()->getResult();
                     
        }

        $contents = $this->renderView('person/person.html.twig', [
                
            'form' => $form_person->createView(),
            'persons' => $persons,
            
            'active' => Person::ACTIVE,
            'banned' => Person::BANNED,
            'deleted' => Person::DELETED,
                
            ] );

        return new Response ($contents);
    }

    public function person_edit (Request $request, $id)
    {
    // person of given id   
        $personManager = $this->getDoctrine()->getManager();
        $person = $personManager->getRepository(Person::class)->find($id);
        
        $login1=$person->getLogin();
        $i_name1=$person->getIName();
        $f_name1=$person->getFName();
        $state1=$person->getStateString();
                
    //form for editing person data 
        $form = $this->createForm (PersonType::class, $person)
                                        ->add('login', TextType::class, ['label'=>'Login:'])
                                        ->add('i_name', TextType::class, ['label'=>'Name:'])
                                        ->add('f_name', TextType::class, ['label'=>'Surname:'])
                                        ->add('state', ChoiceType::class, [
                                                                            'label'=>'Choose the State:',
                                                                            'choices'=> [
                                                                                'Active' => Person::ACTIVE,
                                                                                'Banned' => Person::BANNED,
                                                                                'Deleted' => Person::DELETED,
                                                                                ],
                                                                            'placeholder'=>""])
                                        ->add('save', SubmitType::class, ['label'=> 'Save changes']);
                     
        $form->handleRequest($request);
            
        $save='unsaved';
        if ($form->isSubmitted()) {
            $save='saved';
            $personManager->flush();
        }
                           
        $contents = $this->renderView('person_edit/person_edit.html.twig',
            [
                'form' => $form->createView(),
                'id'=> $id,
                'save'=>$save,

                'login1'=> $login1,
                'i_name1'=> $i_name1,
                'f_name1'=> $f_name1,
                'state1'=> $state1,

                'person' => $person,

                'active' => Person::ACTIVE,
                'banned' => Person::BANNED,
                'deleted' => Person::DELETED,

            ]);
            return new Response($contents);
    }

    public function person_add (Request $request)
    {
        //add new person to DB
        $person = new Person();
        $form = $this->createForm (PersonType::class, $person)
                            
                            ->add('save', SubmitType::class, ['label'=>'Add the Person']);
         
        $form->handleRequest($request);
       
        if ($form->isSubmitted()) {
                          
            $productManager = $this->getDoctrine()->getManager();
            $productManager->persist($person);
            $productManager->flush();
            
            return $this->redirectToRoute('person');
        }
        else 
        {
            $contents = $this->renderView('person_add/person_add.html.twig',
                    [
                        'form' => $form->createView(),
                    ],
                );
            return new Response($contents);
        }
        
    }
}
