<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getUsers(
        UserRepository $userRepository,
        Request $request,
        TagAwareCacheInterface $cache,
        SerializerInterface $serializer
    ): JsonResponse {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        $cacheId = 'users-'.$page.'-'.$limit;
        $users = $cache->get($cacheId, function (ItemInterface $item) use ($userRepository, $page, $limit) {
            $item->expiresAfter(900);
            $item->tag('usersCache');

            return $userRepository->getUsersPaginated($page, $limit);
        });

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUsers = $serializer->serialize($users, 'json', $context);

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'user_details', methods: ['GET'])]
    public function getUserDetails(User $user, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users', name: 'post_user', methods: ['POST'])]
    public function addUser(
        SerializerInterface $serializer,
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorageInterface,
        UrlGeneratorInterface $urlGenerator,
        TagAwareCacheInterface $cache,
        ValidatorInterface $validatorInterface
    ): JsonResponse {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $token = $tokenStorageInterface->getToken();
        $client = $token->getUser();
        $user->setClient($client);
        $errors = $validatorInterface->validate($user);
        if (0 < $errors->count()) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($user);
        $entityManager->flush();

        $cache->invalidateTags(['usersCache']);

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('user_details', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/api/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(
        User $user,
        TagAwareCacheInterface $cache,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $entityManager->remove($user);
        $entityManager->flush();

        $cache->invalidateTags(['usersCache']);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
