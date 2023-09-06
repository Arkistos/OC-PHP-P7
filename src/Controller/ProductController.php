<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'app_product', methods:['GET'])]
    public function getProducts(
        ProductRepository $productRepository, 
        SerializerInterface $serializer,
        Request $request,
        TagAwareCacheInterface $cache
        ): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $cacheId = "getProducts-".$page."-".$limit;
        $products = $cache->get($cacheId, function(ItemInterface $item) use ($productRepository, $page, $limit){
            $item->expiresAfter(900);
            $item->tag("productsCache");
            return $productRepository->findAllPaginated($page, $limit);
        });



        $jsonProducts = $serializer->serialize($products,'json');
        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }
}
