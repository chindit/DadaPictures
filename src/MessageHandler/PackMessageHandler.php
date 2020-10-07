<?php

namespace App\MessageHandler;

use App\Message\PackMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PackMessageHandler implements MessageHandlerInterface
{
	public function __invoke(PackMessage $pack)
	{
		echo 'yo';
		// TODO: Implement __invoke() method.
	}
}
