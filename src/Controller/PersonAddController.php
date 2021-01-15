<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Person;
use App\Form\Type\PersonType;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;


class PersonAddController extends AbstractController
{    
    public function person_add (Request $request)
    {
        $person = new Person();
        $form = $this->createForm (PersonType::class, $person)
                            ->add('save', SubmitType::class, ['label'=>'Add the Person']);
         
        $form->handleRequest($request);
       
        if ($form->isSubmitted()) {
                          
            $productManager = $this->getDoctrine()->getManager();
            $productManager->persist($person);
            $productManager->flush();
            
            return $this->redirectToRoute('product');
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