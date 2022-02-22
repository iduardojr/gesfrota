<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Request;
use PHPBootstrap\Validate\Measure\Max;
use PHPBootstrap\Validate\Measure\Ruler\RulerLength;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\Label;
use PHPBootstrap\Widget\Form\Controls\TextArea;
use Gesfrota\Model\Domain\User;

class RequestFieldSetCancel extends RequestFieldSetStep {
	
	/**
	 * @var integer
	 */
	const STEP_TYPE = Request::CANCELED;
	
	public function __construct() {
		parent::__construct();
		
		$input = new TextArea('justify');
		$input->setSpan(7);
		$input->setRows(4);
		$input->setLength(new Max(250, 'Max. 250 caracteres', RulerLength::getInstance()));
		$input->setPlaceholder('Descreva o motivo do cancelamento da requisição (Max. 250 caracteres)');
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->addFilter('trim');
		$input->addFilter('strip_tags');
		
		
		$this->append(new ControlGroup(new Label('Justificativa', $input), $input));
		
	}
	
	public function toDo(User $user, Request $obj, array $data, EntityManager $em) {
		$obj->toCancel($user, $data['justify']);
	}
	
	public function toArray(Request $obj) {
		return ['justify' => $obj->getJustify()];
	}

}
?>