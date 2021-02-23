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

class AjaxSearchController extends AbstractController
{
    public function ajax_search(Request $request) : JsonResponse
    {
        $key = $request->query->get('q'); 

    // Find rows matching with keyword $key:
    $entityManager = $this->getDoctrine()->getManager();
    $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('pers')
                                            -> from ('App\Entity\Person', 'pers')
                                            
                                            -> setParameter('key', $key)
                                            -> where ('pers.login = :key');


                                            // $queryBuilder->expr()->like('pers.login', ':key')
                                            //->setParameter('login', '%'.addcslashes($key, '%_').'%')
                                            //->setParameter('key', '%'.$key.'%');
                                           // -> orderBy('login', 'ASC');

    $results = $queryBuilder->getQuery()->getResult();
    
    /*$returnArray=array();
    foreach($results as $result) {
        array_push($returnArray, [
                'id' => $result->getId(),
                'text' => $result->getLogin() ],
            );
    }*/
        
    // customize your output as per Select2 requirement:
    return new JsonResponse($results);

    } 
}