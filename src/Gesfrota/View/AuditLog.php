<?php
namespace Gesfrota\View;

use Gesfrota\Services\Log;
use Gesfrota\View\Widget\BuilderForm;
use Gesfrota\View\Widget\Component;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\Uneditable;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Row;
use PHPBootstrap\Widget\Misc\Well;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Form;

class AuditLog extends Component {
	
	/**
	 * @param Log $log
	 * @param Action $cancel
	 */
	public function __construct(Log $log, Action $cancel){
		$this->buildPanel('Segurança', 'Auditória');
		
		$fieldset = new Fieldset('Visualização do Registro #' . $log->getCode());
		$form = new BuilderForm('audit-log');
		$form->append($fieldset);
		$this->component = $form;
		$this->panel->append($this->component);
		
		$input1 = new Uneditable('created');
		$input1->setValue($log->getCreated()->format('d/m/Y H:i:s'));
		$form->buildField('Registrado em', $input1, null, $fieldset);
		
		
		$input1 = new Uneditable('uri');
		$input1->setSpan(7);
		$input1->setValue($log->getReferer());
		$form->buildField('URI', $input1, null, $fieldset);
		
		$input1 = new Uneditable('instance');
		$input1->setSpan(7);
		$input1->setValue($log->getInstance());
		$form->buildField('Objeto', $input1, null, $fieldset);
		
		$input1 = new Uneditable('user');
		$input1->setSpan(1);
		$input1->setValue('#'.$log->getUser()->getCode());
		
		$input2 = new Uneditable('user');
		$input2->setSpan(6);
		$input2->setValue($log->getUser()->getName());
		$form->buildField('Usuário', [$input1, $input2], null, $fieldset);
		
		$input1 = new Uneditable('agency');
		$input1->setSpan(1);
		$input1->setValue('#'.$log->getAgency()->getCode());
		
		$input2 = new Uneditable('user');
		$input2->setSpan(6);
		$input2->setValue($log->getAgency()->getAcronym().' - '.$log->getAgency()->getName());
		$form->buildField('Órgão', [$input1, $input2], null, $fieldset);
		
		$form = new BuilderForm('audit-log-compare');
		$form->setStyle(Form::Inline);
		$this->panel->append($form);
		
		$row = new Row();
		$box1 = new Box(6);
		$box2 = new Box(6);
		$row->append($box1);
		$row->append($box2);
		$form->append($row);
		
		$input1 = new Well('old-value');
		$form->buildField('Valor Antigo', $input1, null, $box1);
		if ($log->getOldValue() == null) {
			$input1->append(new Panel('<code><i>null</i></code>'));
		} else {
			$this->toValue($input1, $log->getOldValue(), $log->getNewValue() == null ? [] : $log->getNewValue());
		}
		
		$input1 = new Well('new-value');
		$form->buildField('Valor Novo', $input1, null, $box2);
		if ($log->getNewValue() == null) {
			$input1->append(new Panel('<code><i>null</i></code>'));
		} else {
			$this->toValue($input1, $log->getNewValue(), $log->getOldValue() == null ? [] : $log->getOldValue());
		}
		
		$form->buildButton('cancel', 'Retornar', $cancel);
	}
	
	private function toValue(Well $well, array $vars, array $compare, $prepend = '') {
		foreach($vars as $var => $val) {
			$label = $prepend. ($prepend ? ( is_string($var) ? '::' . $var : '['.$var.']') : $var);
			if (is_array($val)) {
				$this->toValue($well, $val, isset($compare[$var]) && is_array($compare[$var]) ? $compare[$var] : [], $label);
			} else {
				$equals = true;
				if ( $val != null && ! isset($compare[$var]) || (isset($compare[$var]) && $compare[$var] != $val)) {	
					$equals = false;
				}
				if ($val instanceof \DateTime) {
					$val = $val->format('d/m/Y H:i:s');
				} elseif (is_bool($val)) {
					$val = $val ? 'true' : 'false';
				} elseif($val == null) {
					$val = '<i>null</i>';
				}
				$label = $equals ? $label : '<code>' . $label .'</code>';
				$val = $equals ? $val : '<code>' . $val .'</code>';
				$well->append(new Row(false, [new Panel($label), new Panel($val)]));
			}
		}
	}
	
}
?>