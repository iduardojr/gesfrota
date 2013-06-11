<?php
namespace Sigmat\Plugin;

use PHPBootstrap\Mvc\Plugin;
use PHPBootstrap\Mvc\Routing\Dispatcher;
use PHPBootstrap\Mvc\Http\HttpResponse;
use PHPBootstrap\Mvc\Http\HttpRequest;
use Sigmat\View\Layout;

/**
 * Página nao encontrada
 */
class NotFound implements Plugin {

	/** 
	 * 
	 * @see Plugin::preDispatch()
	 */
	public function preDispatch( HttpRequest $request, HttpResponse $response, Dispatcher $dispatcher = null ) {
		if ( $response->isNotFound() ) {
			$layout = new Layout();
			$layout->setTemplate('layout/404.phtml');
			$uri = $request->getUri();
			if ( preg_match('|^[^\?#]*|', $uri, $match) ) {
				$layout->uri = '/' . trim($match[0], "/ \t\n\r\0\x0B");
			}
			$response->setBody($layout);
			return false;
		} 
	}
	
	/**
	 *
	 * @see Plugin::postDispatch()
	 */
	public function postDispatch( HttpRequest $request, HttpResponse $response, Dispatcher $dispatcher = null ) {

	}
}
?>