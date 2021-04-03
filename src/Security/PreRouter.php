<?php
declare(strict_types=1);


namespace App\Security;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class PreRouter
 * @package AppBundle\Security
 * Handle «OPTIONS» request
 */
final class PreRouter implements EventSubscriberInterface
{
	/**
	 * Subscribe to events
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		// return the subscribed events, their methods and priorities
		return array(
			KernelEvents::REQUEST => [
				['onKernelRequest', 1000]
			],
			KernelEvents::RESPONSE => [
				['onKernelResponse']
			]
		);
	}

	/**
	 * Detect if request is type OPTIONS and skip controller
	 *
	 * @param RequestEvent $event
	 * @throws \InvalidArgumentException
	 */
	public function onKernelRequest(RequestEvent $event) : void
	{
		if (!$event->isMasterRequest()) {
			return;
		}
		if ($event->getRequest()->getRealMethod() === Request::METHOD_OPTIONS) {
			$response = new Response();
			$event->setResponse($response);
		}
	}

	/**
	 * If request is OPTIONS, add allowed methods and headers
	 *
	 * @param ResponseEvent $event
	 * @throws \UnexpectedValueException
	 * @throws \InvalidArgumentException
	 */
	public function onKernelResponse(ResponseEvent $event) : void
	{
		$request = $event->getRequest();

		$response = $event->getResponse();
		$response->headers->set('Access-Control-Allow-Origin', '*');
		$response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PATCH,PUT,DELETE');
		$response->headers->set('Access-Control-Allow-Headers', 'Authorization, Origin, X-Requested-With, Content-Type, Accept');

		if ($request->getRealMethod() === Request::METHOD_OPTIONS) {
			$response->setStatusCode(Response::HTTP_OK);
			$response->setContent(null);
			$event->setResponse($response);
		} else {
			$response->setStatusCode($response->getStatusCode());
			$event->setResponse($response);
		}
	}
}
