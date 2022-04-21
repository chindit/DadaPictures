<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProfileController extends AbstractController
{
	#[Route(name: 'profile_update', methods: ['POST'], path: '/api/profile')]
	public function updateProfile(
		Request $request,
		ValidatorInterface $validator,
		Security $security,
		UserPasswordHasherInterface $passwordHasher,
		EntityManagerInterface $entityManager,
		UserRepository $userRepository
	): JsonResponse
	{
		$constraints = [
			'email' => [new Email()],
			'password' => [new AtLeastOneOf([
				new Blank(),
				new Length(min: 6)
			])],
			'language' => [new Choice(['fr', 'en'])]
		];

		$decodedRequest = json_decode($request->getContent(), true);
		$errors = $validator->validate($decodedRequest, new Collection($constraints));

		if ($errors->count() > 0) {
			return new JsonResponse($errors->get(0)->getMessage(), Response::HTTP_BAD_REQUEST);
		}

		/** @var User $user */
		$user = $security->getUser();

		// Check email unicity
		if ($user->getEmail() !== $decodedRequest['email'] && $userRepository->findOneBy(['email' => $decodedRequest['email']])) {
			return new JsonResponse('This email is already taken', Response::HTTP_BAD_REQUEST);
		}

		// Update profile

		$user->setEmail($decodedRequest['email']);
		$user->setLanguage($decodedRequest['language']);

		if ($decodedRequest['password']) {
			$user->setPassword(
				$passwordHasher->hashPassword(
					$user,
					$decodedRequest['password']
				)
			);
		}

		$entityManager->flush();

		return new JsonResponse(null, Response::HTTP_ACCEPTED);
	}
}
