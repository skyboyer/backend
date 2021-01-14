<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{
    public function product(): Response
    {
        $contents = $this->renderView('product/product.html.twig', [
            'id' => '33'
        ]);
        return new Response ($contents);
    }
}
