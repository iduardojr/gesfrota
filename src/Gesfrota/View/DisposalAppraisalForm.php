<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Disposal;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Measure\Min;
use PHPBootstrap\Validate\Required\EqualTo;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Form\Controls\Uneditable;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Form\Controls\Decorator\InputContext;

class DisposalAppraisalForm extends AbstractForm {
	
	/**
	 * @var Panel
	 */
	protected $flashMessage;
	
	/**
	 * @param Disposal $disposal
	 * @param Action $next
	 * @param Action $cancel
	 * @param DisposalItemTable $table
	 */
	public function __construct( Disposal $disposal, Action $next, Action $cancel, DisposalItemTable $table ) {
	    $this->buildPanel('Minha Frota', 'Gerenciar Disposições para Alienação');
		$form = $this->buildForm('disposal-appraisal-form');
		
		$general = new Fieldset('Disposição #' . $disposal->getCode() . ' <small>' . $disposal->getDescription() . '</small>');
		
		$this->flashMessage = $this->panel->getByName('flash-message');
		$this->panel->remove($this->flashMessage);
		
		$general->append($this->flashMessage);
		$general->append($table);
		
		
		$foot = new Box(['offset'=> 7]);
		$input[0] = new Uneditable('assets-count');
		$input[0]->setSpan(1);
		$input[0]->setValue($disposal->getTotalAssetsValued() . ' / ' . $disposal->getTotalAssets());
		
		$input[1] = new Hidden('assets-total');
		$input[1]->setValue($disposal->getTotalAssets());
		
		
		$input[2] = new Hidden('assets-value');
		$input[2]->addFilter(function($value) {
			return (int) $value;
		});
		$input[2]->setValue($disposal->getTotalAssetsValued());
		$input[2]->setLength(new Min(1, 'Sem ativo(s) para serem encaminhados.'));
		$input[2]->setRequired(new EqualTo(new InputContext($input[1]), 'Todos os ativos devem ser avaliados'));
		
		$form->buildField('Ativos Avaliados', $input, false, $foot);
		$general->append($foot);
		
		$tab = new Tabbable('disposal-tabs');
		$tab->setPlacement(Tabbable::Left);
		
		$link = new NavLink('Seleção');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$tab->addItem(new NavLink('Avaliação'), null, new TabPane($general));
		
		$link = new NavLink('Confirmação');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$form->append($tab);

		$form->buildButton('submit', 'Encaminhar Disposição', $next);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Gesfrota\View\Widget\Component::setAlert()
	 */
	public function setAlert(Alert $alert = null) {
		$this->flashMessage->setContent($alert);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Disposal $object ) {
		
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Disposal $object ) {
		
	}

}
?>