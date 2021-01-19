<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use App\Entity\Product;
use App\Form\Type\ProductType;
use App\Repository\ProductRepository;

use App\Entity\PersonLikeProduct;
use App\Form\Type\PersonLikeProductType;
use App\Repository\PersonLikeProductRepository;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;

class ProductEditController extends AbstractController
{
    public function product_edit (Request $request, $id)
    {
        $productManager = $this->getDoctrine()->getManager();
        $product = $productManager->getRepository(Product::class)->find($id);
        
        if ( $product==null ) {
            return $this->redirectToRoute('product');
        }
        else {
            $name1=$product->getName();
            $date1=date_format($product->getPublicDate(), 'Y-m-d');
            $info1=$product->getInfo();
                
            $form1 = $this->createForm (ProductType::class, $product)
                            ->add('public_date', DateType::class, [
                                'label'=>'Date of publication',
                                'widget' => 'single_text',
                                ] )
                            ->add('save', SubmitType::class, ['label'=> 'Save changes']);
    
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
        $productManager = $this->getDoctrine()->getManager();
        $product = $productManager->getRepository(Product::class)->find($id);
        
        $productManager->remove($product);
        $productManager->flush();

        $personLikeProductManager = $this->getDoctrine()->getManager();
        $personLikeProduct = $this->getDoctrine()->getRepository(PersonLikeProduct::class)
                                                ->findBy ([
                                                    'product' => $id 
                                                ]);
            
        foreach ($personLikeProduct as $persprod) {
            $personLikeProductManager->remove($persprod);
            $personLikeProductManager->flush();
        }
        return $this->redirectToRoute('product');
    }

    



}