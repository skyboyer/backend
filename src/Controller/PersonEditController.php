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

class PersonEditController extends AbstractController
{
    public function person_edit (Request $request, $id)
    {
        $personManager = $this->getDoctrine()->getManager();
        $person = $personManager->getRepository(Person::class)->find($id);
        
        $login1=$person->getLogin();
        $i_name1=$person->getIName();
        $f_name1=$person->getFName();
        $state1=$person->getState();
                
        $form = $this->createForm (PersonType::class, $person)
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
}