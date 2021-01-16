<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Product;
use App\Form\Type\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\DateTimeInterface;
use Symfony\Component\Validator\Constraints\DateTime;


class ProductAddController extends AbstractController
{
    
    public function product_add (Request $request)
    {
        $product = new Product();
        $product->setPublicDate(date_create());
        
        $form = $this->createForm (ProductType::class, $product)
                ->add('public_date', DateType::class, [
                                'label'=>'Date of publication',
                                'widget' => 'single_text',
                                                 
                                
                                ] )
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