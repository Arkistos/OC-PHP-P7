<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getProducts(
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        Request $request,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $cacheId = 'getProducts-'.$page.'-'.$limit;
        $products = $cache->get($cacheId, function (ItemInterface $item) use ($productRepository, $page, $limit) {
            $item->expiresAfter(1);
            $item->tag('productsCache');

            return $productRepository->findAllPaginated($page, $limit);
        });

        $products = new PaginatedRepresentation(new CollectionRepresentation($products['products']),
            'products',
            [],
            $page,
            $limit,
            $products['pages']
        );

        $jsonProducts = $serializer->serialize($products, 'json');

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'product_details', methods: ['GET'])]
    public function getProduct(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, 'json');

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
