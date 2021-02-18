<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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


class ProductModuleController extends AbstractController
{
    public function product(Request $request) : Response
    {     
    // filter for products by name and date
        $form = $this->createFormBuilder()
                    ->setMethod('GET')
                    ->add('name', TextType::class, ['label'=>'Name:',
                                        'required' => false])
                    ->add('date_from', DateType::class, ['label'=>'Date from:',
                                                'required' => false,
                                                'widget' => 'single_text',
                                                'html5' => false,
                                                'attr' => ['class' => 'js-datepicker', 'readonly'=>'readonly' ],
                                                ])
                    ->add('date_to', DateType::class, ['label'=>'Date to:',
                                                'required' => false,
                                                'widget' => 'single_text',
                                                'html5' => false,
                                                'attr' => ['class' => 'js-datepicker', 'readonly'=>'readonly'],
                                                ])
                    ->add('send', SubmitType::class, ['label'=>'Show the chosen products'])
                    ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() ) {
            
            $data = $form->getData();
            $date_from=$data['date_from'];
            $date_to=$data['date_to'];
            $name=$data['name'];

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
                $queryBuilder=$queryBuilder->setParameter('name', strtolower($name))
                                            ->andwhere ($queryBuilder->expr()->eq(
                                                       $queryBuilder-> expr()->lower('p.name'), ':name') ) ;
            }
            $products = $queryBuilder->getQuery()->getResult();

        }

        else {

           $products = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();
        }                
        
        $contents = $this->renderView('product/product.html.twig', [
                
            'form' => $form->createView(),
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
                
            $form1 = $this->createForm (ProductType::class, $product)
                            ->add('public_date', DateType::class, [
                                'label'=>'Date of publication',
                                'widget' => 'single_text',
                                'html5' => false,
                                'attr' => ['class' => 'js-datepicker', 'readonly'=>'readonly'] ]) //adding  datepicker
                            ->add('save', SubmitType::class, ['label'=> 'Save changes']);
    
    //button for deleting product    
            $form2 = $this->createFormBuilder()
                ->add('send', SubmitType::class, ['label'=>'Delete the Product!!'])
                ->getForm();
            
            $form1->handleRequest($request);
            $form2->handleRequest($request);

            $save='unsaved';
            if ($form1->isSubmitted()) {
                $save='saved';
                $productManager->flush();
                               
            }
            elseif ($form2->isSubmitted()) {
                
                return $this->redirectToRoute('product_delete', ['id' => $id]);
                
            }
              
            $contents = $this->renderView('product_edit/product_edit.html.twig',
                [
                    'form1' => $form1->createView(),
                    'form2' => $form2->createView(),
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
       
        
    // remove product's relations (without cascade)
        
        /*$personLikeProductManager = $this->getDoctrine()->getManager();
        $personLikeProduct = $this->getDoctrine()->getRepository(PersonLikeProduct::class)
                                                ->findBy ([
                                                    'product' => $id 
                                                ]);
            
        foreach ($personLikeProduct as $persprod) {
            $personLikeProductManager->remove($persprod);
            $personLikeProductManager->flush();
        }*/

    //remove product from DB
        $productManager = $this->getDoctrine()->getManager();
        $product = $productManager->getRepository(Product::class)->find($id);
        $productManager->remove($product);
        $productManager->flush();

        return $this->redirectToRoute('product');
    }

    public function product_add (Request $request)
    {
    //ading product to DB 
        $product = new Product();
        $product->setPublicDate(date_create());
        
        $form = $this->createForm (ProductType::class, $product)
                ->add('public_date', DateType::class, [
                                'label'=>'Date of publication',
                                'widget' => 'single_text',
                                'html5' => false,
                                'attr' => ['class' => 'js-datepicker', 'readonly'=>'readonly'] ])
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
