<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\AbstractType;


use App\Entity\Person;
use App\Form\Type\PersonType;
use App\Repository\PersonRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;


class PersonController extends AbstractController
{
    public function person(Request $request) : Response
    {   
        
        $form = $this->createFormBuilder()
                    ->setMethod('GET')
                    ->add('login', TextType::class, ['label'=>'Login (ATTENTION ON REGISTER!):',
                                        'required' => false])
                    ->add('i_name', TextType::class, ['label'=>'Name:',
                                                'required' => false])
                    ->add('f_name', TextType::class, ['label'=>'Family:',
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
                    ->add('send', SubmitType::class, ['label'=>'Show the chosen users'])
                    ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() ) {
            
            $data = $form->getData();
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
                
            'form' => $form->createView(),
            'persons' => $persons,
            
            'active' => Person::ACTIVE,
            'banned' => Person::BANNED,
            'deleted' => Person::DELETED,
                
            ] );

        return new Response ($contents);
    }
}
