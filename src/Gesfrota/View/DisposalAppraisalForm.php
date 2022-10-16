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
use PHPBootstrap\Widget\Action\TgWindows;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Layout\Row;

class DisposalAppraisalForm extends AbstractForm {
	
	/**
	 * @var Panel
	 */
	protected $flashMessage;
	
	/**
	 * @param Disposal $disposal
	 * @param DisposalItemTable $table
	 * @param Action $appraise
	 * @param Action $print
	 * @param Action $export
	 * @param Action $cancel
	 */
	public function __construct( Disposal $disposal, DisposalItemTable $table, Action $appraise, Action $print, Action $export, Action $cancel) {
	    $this->buildPanel('Minha Frota', 'Gerenciar Disposições para Alienação');
		$form = $this->buildForm('disposal-appraisal-form');
		
		$general = new Fieldset('Disposição #' . $disposal->getCode() . ' <small>' . $disposal->getDescription() . '</small>');
		
		$this->flashMessage = $this->panel->getByName('flash-message');
		$this->panel->remove($this->flashMessage);
		
		$general->append($this->flashMessage);
		$general->append($table);
		
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

		$form->buildButton('submit', [new Icon('icon-ok-sign', true), 'Finalizar Avaliação'], $appraise);
		$form->buildButton('print', [new Icon('icon-print'), 'Imprimir Disposição'], new TgWindows($print, 1024, 762));
		$form->buildButton('export', [new Icon('icon-share-alt'), 'Exportar CSV'], $export);
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