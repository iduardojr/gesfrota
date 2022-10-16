<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Disposal;
use Gesfrota\Model\Domain\DisposalLot;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\ArrayDatasource;
use PHPBootstrap\Validate\Measure\Max;
use PHPBootstrap\Validate\Measure\Ruler\RulerLength;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgWindows;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\TextArea;
use PHPBootstrap\Widget\Form\Controls\Uneditable;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Layout\Row;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;

class DisposalConfirmForm extends AbstractForm {
	
	/**
	 * @var Panel
	 */
	protected $flashMessage;
	
	/**
	 * @param Disposal $disposal
	 * @param Action $cancel
	 * @param Action $printAsset
	 * @param Action $print
	 * @param Action $export
	 * @param Action $confirm
	 * @param Action $decline
	 * @param Action $devolve
	 */
	public function __construct( Disposal $disposal, DisposalItemTable $table, Action $print, Action $export, Action $cancel, Action $confirm = null, Action $decline = null, Action $devolve = null ) {
    	$this->buildPanel('Minha Frota', 'Gerenciar Disposições para Alienação');
		$form = $this->buildForm('disposal-confirm-form');
		$form->setStyle(null);
		
		$allowed = Disposal::getStatusAllowed();
		$status = $disposal->getStatus();
		switch ( $status ) {
			case Disposal::APPRAISED:
				$label = '<span class="label label-warning">' . $allowed[$status] . '</span>';
				break;
				
			case Disposal::CONFIRMED:
				$label = '<span class="label label-success">' . $allowed[$status] . '</span>';
				break;
				
			case Disposal::DECLINED:
				$label = '<span class="label label-important">' . $allowed[$status] . '</span>';
				break;
				
			case Disposal::FORWARDED:
			    $label = '<span class="label label-info">' . $allowed[$status] . '</span>';
			    break;
		}
		
		$general = new Fieldset('Disposição #' . $disposal->getCode() . ' <small>' . $disposal->getDescription() . '</small> ' . $label);
		
		$this->flashMessage = $this->panel->getByName('flash-message');
		$this->panel->remove($this->flashMessage);
		$general->append($this->flashMessage);
		
        $general->append($table);
        
        
		$tab = new Tabbable('disposal-tabs');
		$tab->setPlacement(Tabbable::Left);
		
		$link = new NavLink('Seleção');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$link = new NavLink('Avaliação');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$tab->addItem(new NavLink('Confirmação'), null, new TabPane(new Box(0, [$general])));
		
		$form->append($tab);
        if ( $devolve ) {
            $form->buildButton('devolve', [new Icon('icon-backward', true), 'Retornar Disposição'], $devolve, Button::Inverse);
        }
        if ($confirm) {
            $form->buildButton('confirm', [new Icon('icon-thumbs-up', true), 'Confirmar Disposição'], new TgFormSubmit($confirm, $form, false), Button::Success);
        } 
		if ($decline) {
			$body = new Box();
			
			$input = new TextArea('justify');
			$input->setSpan(5);
			$input->setRows(4);
			$input->setLength(new Max(250, 'Max. 250 caracteres', RulerLength::getInstance()));
			$input->setPlaceholder('Descreva o motivo da recusa da disposição (Max. 250 caracteres)');
			$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
			
			$form->buildField('Justificativa', $input, null, $body);
			
			$modalDecline = new Modal('disposal-decline', new Title('Recusar Disposição', 3));
			$modalDecline->setWidth(700);
			$modalDecline->setBody($body);
			$modalDecline->addButton(new Button([new Icon('icon-thumbs-down', true), 'Recusar Disposição'], new TgFormSubmit($decline, $form), Button::Danger));
			$modalDecline->addButton(new Button('Cancelar', new TgModalClose()));
			
			$general->append($modalDecline);
			
			$form->buildButton('decline', [new Icon('icon-thumbs-down', true),'Recusar Disposição'], new TgModalOpen($modalDecline), Button::Danger);
		}
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