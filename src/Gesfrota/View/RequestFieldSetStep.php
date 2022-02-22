<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Request;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Layout\Panel;
use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\User;

abstract class RequestFieldSetStep extends Fieldset {
	
	public function __construct() {
		parent::__construct(null);
		
		$this->append(new Panel(null, 'alert-message'));
	}
	
	/**
	 * @return integer
	 */
	public function getStepType() {
		return constant(get_class($this) . '::STEP_TYPE');
	}
	
	/**
	 * @param Request $obj
	 */
	public function create(Request $obj) {
		
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