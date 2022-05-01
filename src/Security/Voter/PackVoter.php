<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Pack;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PackVoter extends Voter
{
	public const DELETE = 'delete';
	public const EDIT = 'edit';

	protected function supports(string $attribute, mixed $subject): bool
	{
		if (!in_array($attribute, [self::DELETE, self::EDIT])) {
			return false;
		}

		if (!$subject instanceof Pack) {
			return false;
		}

		return true;
	}

	protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
	{
		$user = $token->getUser();

		if (!$user instanceof User) {
			// the user must be logged in; if not, deny access
			return false;
		}

		// you know $subject is a Post object, thanks to `supports()`
		/** @var Pack */
		$pack = $subject;

		return match($attribute) {
			self::EDIT => in_array('ROLE_ADMIN', $user->getRoles()) || $pack->getCreator() === $user,
			self::DELETE => in_array('ROLE_ADMIN', $user->getRoles()) || $pack->getCreator() === $user,
			default => false
		};
	}
}
