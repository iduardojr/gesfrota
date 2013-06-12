<?php
namespace Sigmat\Common\Plugin;

use PHPBootstrap\Mvc\Plugin;
use PHPBootstrap\Mvc\Routing\Dispatcher;
use PHPBootstrap\Mvc\Http\HttpResponse;
use PHPBootstrap\Mvc\Http\HttpRequest;
use Sigmat\View\Layout;

/**
 * Erro
 */
class Error implements Plugin {

	/** 
	 * 
	 * @see Plugin::preDispatch()
	 */
	public function preDispatch( HttpRequest $request, HttpResponse $response, Dispatcher $dispatcher = null ) {
		
	}
	
	/**
	 *
	 * @see Plugin::postDispatch()
	 */
	public function postDispatch( HttpRequest $request, HttpResponse $response, Dispatcher $dispatcher = null ) {
		if ( $response->isServerError() ) {
			$layout = new Layout();
			$layout->setTemplate('layout/500.phtml');
			if ( $dispatcher ) {
				$layout->exception = $dispatcher->getException();
			}
			$response->setBody($layout);
		}
	}
}
?>