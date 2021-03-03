<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Validator\Constraints\DateTimeInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\AbstractType;


use App\Entity\Product;
use App\Form\Type\ProductType;
use App\Repository\ProductRepository;
use App\Entity\PersonLikeProduct;
use App\Form\Type\PersonLikeProductType;
use App\Repository\PersonLikeProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;

use App\Controller\ProductModuleController;
use Doctrine\ORM\Query\ResultSetMapping;

class ProductModuleController extends AbstractController
{
    public function product(Request $request) : Response
    {     
    // filter for products by name and date
        $product = new Product();
        $form_product = $this->createForm (ProductType::class, $product,['method' => 'GET'])
                    ->add('name', TextType::class, ['label'=>'Name:',
                                                    'required' => false])
                    ->add('send', SubmitType::class, ['label'=>'Show the chosen products']);
        
        $form_product->handleRequest($request);

        if ($form_product->isSubmitted() ) {
            
            $name=$form_product->get('name')->getData();
            $date_from=$form_product->get('date_from')->getData();
            $date_to=$form_product->get('date_to')->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $queryBuilder = $entityManager->createQueryBuilder()
                                            -> select('prod')
                                            -> from ('App\Entity\Product', 'prod')
                                            -> orderBy('prod.public_date', 'ASC');
            if (isset($date_from) ) {
                $date_from=date_format($date_from, 'Y-m-d');
                $queryBuilder=$queryBuilder->setParameter('date_from', $date_from)
                                            ->andwhere ('prod.public_date >= :date_from');
            }
                                           
            if (isset($date_to) ) {
                $date_to=date_format($date_to, 'Y-m-d');
                $queryBuilder=$queryBuilder->setParameter('date_to', $date_to)
                                            ->andwhere ('prod.public_date<= :date_to');
            }
            if (isset($name)) {
                $queryBuilder=$queryBuilder->setParameter('name', addcslashes($name, '%_').'%') 
                                            -> andWhere('prod.name LIKE :name');
            }
            $products = $queryBuilder->getQuery()->getResult();
        }

        else {

           $products = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();
        }                
        
        $contents = $this->renderView('product/product.html.twig', [
                
            'form' => $form_product->createView(),
            'products' => $products,
                
            ]);
        return new Response ($contents);
    }

    public function product_edit (Request $request, $id)
    {
        
    // product of given id    
        $productManager = $this->getDoctrine()->getManager();
        $product = $productManager->getRepository(Product::class)->find($id);
    
    //if product is already deleted,  the page is not accessable for the id    
        if ( $product==null ) {
            return $this->redirectToRoute('product');
        }
    
    //form for editing product data
        else {
            $name1=$product->getName();
            $date1=date_format($product->getPublicDate(), 'Y-m-d');
            $info1=$product->getInfo();
             
            $form_product = $this->createForm (ProductType::class, $product)
                                ->add('name', TextType::class, ['label'=>'Name:'])
                                ->add('info', TextareaType::class, ['label'=>'Product information:'])
                                ->add('public_date', DateType::class, [
                                                'label'=>'Date of publication',
                                                'widget' => 'single_text',
                                                'html5' => false,
                                                'attr' => ['class' => 'js-datepicker', 'readonly'=>'readonly'] ])
                                ->add('date_from', HiddenType::class, ['mapped'=>false])
                                ->add('date_to', HiddenType::class, ['mapped'=>false])
                                ->add('save', SubmitType::class, ['label'=> 'Save changes']);

                
    //button for deleting product    
            $form_delete = $this->createFormBuilder()
                ->add('send', SubmitType::class, ['label'=>'Delete the Product!!'])
                ->getForm();
            
            $form_product->handleRequest($request);
            $form_delete->handleRequest($request);

            $save='unsaved';

            if ($form_product->isSubmitted()) {
                $save='saved';
                $productManager->flush();
            }
            elseif ($form_delete->isSubmitted()) {
                return $this->redirectToRoute('product_delete', ['id' => $id]);
            }
              
            $contents = $this->renderView('product_edit/product_edit.html.twig',
                [
                    'form_product' => $form_product->createView(),
                    'form_delete' => $form_delete->createView(),
                    'id'=> $id,
                    'save' => $save,

                    'product'=> $product,
                                                   
                    'name1'=> $name1,
                    'date1'=> $date1,
                    'info1'=> $info1,

                ]  );
                return new Response($contents);
            
            
        }
    }
       
    public function product_delete ($id)
    {
//some code for learning purposes:
    
    // remove product's relations without taking large quantities into account, in iteration
        /*$personLikeProductManager = $this->getDoctrine()->getManager();
        $personLikeProduct = $this->getDoctrine()->getRepository(PersonLikeProduct::class)
                                                ->findBy ([
                                                    'product' => $id 
                                                ]);
            
        foreach ($personLikeProduct as $persprod) {
            $personLikeProductManager->remove($persprod);
            $personLikeProductManager->flush();
        }*/


    // remove product's relations with queryBuilder:
         /* $personLikeProductManager = $this->getDoctrine()->getManager();
        $queryBuilder = $personLikeProductManager->createQueryBuilder()
                                                    -> delete ('App\Entity\PersonLikeProduct','plp')
                                                    -> setParameter('product_id', $id)
                                                    -> andwhere ('plp.product = :product_id');
        $query = $queryBuilder->getQuery();
        $query->execute();   */

    // remove product's relations with raw SQL:
        /*$SQLquery="DELETE FROM person_like_product AS plp WHERE  plp.product_id = :id";
            
        $entityManager = $this->getDoctrine()->getManager();
        $stmt=$entityManager->getConnection()->prepare($SQLquery);
        $stmt->bindValue('id', $id);
        $stmt->execute();*/

    //remove product from DB simple, all assotiations are removed thanks to "cascade={"remove"}"-annotation in property $ProductHavePersons in Product class:
        $productManager = $this->getDoctrine()->getManager();
        $product = $productManager->getRepository(Product::class)->find($id);
        $productManager->remove($product);
        $productManager->flush(); 

//some code for learning purposes:
    
    //remove product from DB with DQL:
        /*$productManager = $this->getDoctrine()->getManager();
        $DQLquery = $productManager->createQuery("  DELETE App\Entity\Product p 
                                                    WHERE p.id = :id ");
        $DQLquery->setParameter('id', $id);
        $DQLquery->execute(); */

        return $this->redirectToRoute('product');
    }

    public function product_add (Request $request)
    {
    //ading product to DB 
        $product = new Product();
        $product->setPublicDate(date_create());
        
        $form = $this->createForm (ProductType::class, $product)
                                ->add('name', TextType::class, ['label'=>'Name:'])
                                ->add('info', TextareaType::class, ['label'=>'Product information:'])
                                ->add('public_date', DateType::class, [
                                                'label'=>'Date of publication',
                                                'widget' => 'single_text',
                                                'html5' => false,
                                                'attr' => ['class' => 'js-datepicker', 'readonly'=>'readonly'] ])
                                ->add('date_from', HiddenType::class, ['mapped'=>false])
                                ->add('date_to', HiddenType::class, ['mapped'=>false])
                                ->add('save', SubmitType::class, ['label'=>'Add the product']);
         
        $form->handleRequest($request);
       
        if ($form->isSubmitted()) {
                 
            $productManager = $this->getDoctrine()->getManager();
            $productManager->persist($product);
            $productManager->flush();
            
            return $this->redirectToRoute('product');
        }
        else 
        {
            $contents = $this->renderView('product_add/product_add.html.twig',
                    [
                        'form' => $form->createView(),
                    ],
                );
            return new Response($contents);
        }
        
    }

}
