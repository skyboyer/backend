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

use App\Controller\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use Symfony\Component\HttpFoundation\Session\SessionInterface;


class AjaxSearchController extends AbstractController
{
    public function ajax_search_person_i(Request $request) : Response {
        $key = $request->query->get('q'); 

        $entityManager = $this->getDoctrine()->getManager();
        $queryBuilder = $entityManager->createQueryBuilder()
                                                -> select('pers')
                                                -> from ('App\Entity\Person', 'pers')
                                                -> setParameter('key', '%'.addcslashes($key, '%_').'%') 
                                                -> where ('pers.i_name LIKE :key');
        $results = $queryBuilder->getQuery()->getResult();

        $returnArray=array();
        $i=0;
        foreach($results as $result) {
            if ( empty($returnArray) ) {
                array_push($returnArray, [ 'id' => $result->getIName(), 'text' => $result->getIName() ] );
            }
            else {    
                if ( $result->getIName()!= $returnArray[$i]['text'] ) {
                    array_push($returnArray, [ 'id' => $result->getIName(), 'text' => $result->getIName() ]  );
                    $i=$i+1;
                }
            }
        };
        return $this->json($returnArray);
    }

    public function ajax_search_person_f(Request $request) : Response {
        $key = $request->query->get('q'); 

        $entityManager = $this->getDoctrine()->getManager();
        $queryBuilder = $entityManager->createQueryBuilder()
                                                -> select('pers')
                                                -> from ('App\Entity\Person', 'pers')
                                                -> setParameter('key', '%'.addcslashes($key, '%_').'%') 
                                                -> where ('pers.f_name LIKE :key');
        $results = $queryBuilder->getQuery()->getResult();

        $returnArray=array();
        $i=0;
        foreach($results as $result) {
            if ( empty($returnArray) ) {
                array_push($returnArray, [ 'id' => $result->getFName(), 'text' => $result->getFName() ] );
            }
            else {    
                if ( $result->getFName()!= $returnArray[$i]['text'] ) {
                    array_push($returnArray, [ 'id' => $result->getFName(), 'text' => $result->getFName() ]  );
                    $i=$i+1;
                }
            }
        };
        return $this->json($returnArray);
    }

    public function ajax_search_product_name(Request $request) : Response {
        $key = $request->query->get('q'); 

        $entityManager = $this->getDoctrine()->getManager();
        $queryBuilder = $entityManager->createQueryBuilder()
                                                -> select('prod')
                                                -> from ('App\Entity\Product', 'prod')
                                                -> setParameter('key', '%'.addcslashes($key, '%_').'%') 
                                                -> where ('prod.name LIKE :key');
        $results = $queryBuilder->getQuery()->getResult();

        $returnArray=array();
        $i=0;
        foreach($results as $result) {
            if ( empty($returnArray) ) {
                array_push($returnArray, [ 'id' => $result->getName(), 'text' => $result->getName() ] );
            }
            else {    
                if ( $result->getName()!= $returnArray[$i]['text'] ) {
                    array_push($returnArray, [ 'id' => $result->getName(), 'text' => $result->getName() ]  );
                    $i=$i+1;
                }
            }
        };
        return $this->json($returnArray);
    }

    public function ajax_search_person_login(Request $request) : Response {
        $key = $request->query->get('q'); 

        // Find rows matching with keyword $key - queryBuilder:
        $entityManager = $this->getDoctrine()->getManager();
        $queryBuilder = $entityManager->createQueryBuilder()
                                                -> select('pers')
                                                -> from ('App\Entity\Person', 'pers')
                                                -> setParameter('key', '%'.addcslashes($key, '%_').'%')  //more secure, see https://stackoverflow.com/questions/3755718/doctrine2-dql-use-setparameter-with-wildcard-when-doing-a-like-comparison
                                                //->setParameter('key', '%'.$key.'%') //less secure

                                                -> where ('pers.login LIKE :key');
        $results = $queryBuilder->getQuery()->getResult();

        
//some code for learning purposes:

    //Find rows matching with keyword $key - DQL:

        /*$productManager = $this->getDoctrine()->getManager();
        $DQLquery = $productManager->createQuery(  
                                                "SELECT App\Entity\Person pers 
                                                    WHERE p.id = :id "
                                                );
        $DQLquery->setParameter('id', $id);
        $DQLquery->execute(); */

        $returnArray=array();
        $i=0;
        foreach($results as $result) {
            if ( empty($returnArray) ) {
                array_push($returnArray, ['id' => $result->getLogin(), 'text' => $result->getLogin() ] );
            }
            else {    
                if ( $result->getLogin()!= $returnArray[$i]['text'] ) {
                    array_push ($returnArray, [ 'id' => $result->getLogin(), 'text' => $result->getLogin()]  );
                    $i=$i+1;
                }
            }
        };

        return $this->json($returnArray);
    }



}