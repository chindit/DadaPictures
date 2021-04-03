<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserController extends AbstractController
{
	#[Route('/api/login', methods: ["POST"])]
    public function login(Request $request, UserRepository $repository, EncoderFactoryInterface $encoder, NormalizerInterface $serializer): JsonResponse
    {
    	$requestData = $request->toArray();
        if (!$requestData['username'] || !$requestData['password']) {
            return new JsonResponse(['message' => 'Missing username or password'], Response::HTTP_BAD_REQUEST);
        }

        $user = $repository->findOneBy(['username' => $requestData['username']]);

        if ($user === null) {
            return new JsonResponse(['message' => 'Invalid username'], Response::HTTP_BAD_REQUEST);
        }

        if (!$encoder->getEncoder($user)->isPasswordValid($user->getPassword(), $requestData['password'], $user->getSalt())) {
            return new JsonResponse(['message' => 'Invalid password'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($serializer->normalize($user));
    }

	#[Route('/api/register', methods: ["POST"])]
	public function register(
		Request $request,
		UserRepository $repository,
		EncoderFactoryInterface $encoder,
		EntityManagerInterface $entityManager
	): JsonResponse
	{
		$requestData = $request->toArray();
		if (!$requestData['username'] || !$requestData['password'] || !$requestData['email']) {
			return new JsonResponse(['message' => 'Missing username, email or password'], Response::HTTP_BAD_REQUEST);
		}

		if ($repository->findOneBy(['username' => $requestData['username']])) {
			return new JsonResponse(['message' => 'Username already exists'], Response::HTTP_BAD_REQUEST);
		}

		if (!filter_var($requestData['email'], FILTER_VALIDATE_EMAIL) || $repository->findOneBy(['email' => $requestData['email']])) {
			return new JsonResponse(['message' => 'Email already exists or is invalid'], Response::HTTP_BAD_REQUEST);
		}

		$password = $encoder->getEncoder(new User())->encodePassword($requestData['password'], '');

		$user = (new User())
			->setUsername($requestData['username'])
			->setEmail($requestData['email'])
			->setPassword($password)
			->setRoles(['ROLE_USER']);

		$entityManager->persist($user);
		$entityManager->flush();

		return new JsonResponse(null, Response::HTTP_CREATED);
	}
}
