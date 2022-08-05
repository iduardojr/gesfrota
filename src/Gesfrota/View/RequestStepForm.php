<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Request;
use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Layout\Panel;

abstract class RequestStepForm extends AbstractForm {
    
    /**
     * @var User
     */
    private $user;
	
	public function buildPanel($title = null, $subtext = null) {
	    if ( ! isset($this->panel) ) {
	        $panel = new Fieldset($title . ($subtext ? '<small>' . $subtext . '</small>' : ''));
	        $this->alert = new Panel(null, 'alert-message');
	        $panel->append($this->alert);
	        $this->panel = $panel;
	    }
	    return $this->panel;
	}
	
	/**
	 * @param User $user
	 */
	public function initialize(User $user) {
	    $this->user = $user;
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Request $object ) {
	   $this->component->setData($this->toArray($object));
	}
	
	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Request $object, EntityManager $em ) {
	   $data = $this->component->getData();
	   $this->toDo($this->user, $object, $data, $em);
	}
	
	/**
	 * @param Request $obj
	 * @return array
	 */
	abstract public function toArray(Request $obj);
	
	/**
	 * @param User $user
	 * @param Request $obj
	 * @param array $data
	 * @param EntityManager $em
	 */
	abstract public function toDo(User $user, Request $obj, array $data, EntityManager $em);

}
?>