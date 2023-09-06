<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'app_product', methods:['GET'])]
    public function getProducts(
        ProductRepository $productRepository, 
        SerializerInterface $serializer,
        Request $request
        ): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $products = $productRepository->findAllPaginated($page, $limit);
        $jsonProducts = $serializer->serialize($products,'json');
        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }
}
