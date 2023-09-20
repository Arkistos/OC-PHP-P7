<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hateoas\HateoasBuilder;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
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
        SerializerInterface $serializer,
        TokenStorageInterface $tokenStorageInterface
    ): JsonResponse {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        /**
         * @var Client $client
         */
        $client = $tokenStorageInterface
                    ->getToken()
                    ->getUser();

        $cacheId = 'users-'.$page.'-'.$limit.'-'.$client->getId();
        $users = $cache->get($cacheId, function (ItemInterface $item) use ($userRepository, $page, $limit, $client) {
            $item->expiresAfter(1);
            $item->tag('usersCache');

            return $userRepository->getUsersPaginated($client, $page, $limit);
        });

        $users = new PaginatedRepresentation(new CollectionRepresentation($users['users']),
            'users',
            [],
            $page,
            $limit,
            $users['pages']
        );

        // $hateoas = HateoasBuilder::create()->setDefaultJsonSerializer($serializer)->build();
        // $json = $hateoas->serialize($users, 'json', SerializationContext::create()->setGroups(['getUsers']));

        // $context = SerializationContext::create()->enableMaxDepthChecks();
        // context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUsers = $serializer->serialize($users, 'json');

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'user_details', methods: ['GET'])]
    public function getUserDetails(
        User $user,
        SerializerInterface $serializer,
        TokenStorageInterface $tokenStorageInterface
    ): JsonResponse {
        /**
         * @var Client $client
         */
        $client = $tokenStorageInterface
                    ->getToken()
                    ->getUser();
        if ($user->getClient()->getId() != $client->getId()) {
            return new JsonResponse('', Response::HTTP_FORBIDDEN, [], true);
        }

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
        $client = $tokenStorageInterface
                    ->getToken()
                    ->getUser();
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
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorageInterface
    ): JsonResponse {
        /**
         * @var Client $client
         */
        $client = $tokenStorageInterface
                    ->getToken()
                    ->getUser();
        if ($user->getClient()->getId() != $client->getId()) {
            return new JsonResponse('', Response::HTTP_FORBIDDEN, [], true);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $cache->invalidateTags(['usersCache']);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
