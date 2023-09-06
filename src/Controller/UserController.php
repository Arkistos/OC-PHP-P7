<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface as SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods:['GET'])]
    public function getUsers(
        UserRepository $userRepository,
        Request $request,
        TagAwareCacheInterface $cache,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        $cacheId = 'users-'.$page.'-'.$limit;
        $users = $cache->get($cacheId, function(ItemInterface $item) use ($userRepository, $page, $limit){
            $item->expiresAfter(900);
            $item->tag('usersCache');
            return $userRepository->getUsersPaginated($page, $limit);
        });

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUsers = $serializer->serialize($users, 'json', $context);

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name:'user_details', methods:['GET'])]
    public function getUserDetails(User $user, SerializerInterface $serializer):JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $serializer->serialize($user,'json', $context);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }
}
