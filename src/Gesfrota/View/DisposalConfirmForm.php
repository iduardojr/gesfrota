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
		
        $footer1 = new Box(2);
        
        $input = new Uneditable('assets-amout');
        $input->setSpan(2);
        $input->setValue($disposal->getAmountAssets());
        $form->buildField('Total de Ativos', $input, false, $footer1);
        
        $input = new Uneditable('assets-value');
        $input->setSpan(2);
        $input->setValue('R$ ' . number_format($disposal->getTotalValue(), 2, ',', '.'));
        $form->buildField('Arrecadação Estimada', $input, false, $footer1);
        
        $input = new Uneditable('assets-debit');
        $input->setSpan(2);
        $input->setValue('R$ ' . number_format($disposal->getTotalDebit(), 2, ',', '.'));
        $form->buildField('Total de Débitos', $input, false, $footer1);
        
        $footer2 = new Box(10);
        
        if ( ! $disposal instanceof DisposalLot ) {
            $input = new Uneditable('agency');
            $input->setSpan(2);
            $input->setValue($disposal->getAgency()->getAcronym());
            $form->buildField('Disponibilizado Por', $input, false, $footer2);
            $form->unregister($input);
            
            $row = new Row(true);
            
            $box = new Box(8);
            $input = [];
            $input[0] = new Uneditable('appraised-by-nif');
            $input[0]->setSpan(2);
            $input[0]->setValue($disposal->getAppraisedBy()->getNif());
            
            $input[1] = new Uneditable('appraised-by-name');
            $input[1]->setSpan(4);
            $input[1]->setValue($disposal->getAppraisedBy()->getName());
            
            $form->buildField('Avaliador Responsável', $input, false, $box);
            $form->unregister($input[0]);
            $form->unregister($input[1]);
            $row->append($box);
            
            $box = new Box(3);
            $input = new Uneditable('appraised-at');
            $input->setSpan(2);
            $input->setValue($disposal->getAppraisedAt()->format('d/m/Y H:i:s'));
            $form->buildField('Avaliado em', $input, false, $box);
            $form->unregister($input);
            $row->append($box);
            
            $footer2->append($row);
            
            if ($disposal->getConfirmedAt() ) {
                $row = new Row(true);
                
                $box = new Box(8);
                $input = [];
                $input[0] = new Uneditable('confirmed-by-nif');
                $input[0]->setSpan(2);
                $input[0]->setValue($disposal->getConfirmedBy()->getNif());
                
                $input[1] = new Uneditable('confirmed-by-name');
                $input[1]->setSpan(4);
                $input[1]->setValue($disposal->getConfirmedBy()->getName());
                
                $form->buildField('Confirmado Por', $input, false, $box);
                $form->unregister($input[0]);
                $form->unregister($input[1]);
                $row->append($box);
                
                $box = new Box(3);
                $input = new Uneditable('confirmed-at');
                $input->setSpan(2);
                $input->setValue($disposal->getConfirmedAt()->format('d/m/Y H:i:s'));
                $form->buildField('Confirmado em', $input, false, $box);
                $form->unregister($input);
                $row->append($box);
                
                $footer2->append($row);
            }
            
            if ($disposal->getDeclinedAt() ) {
                $row = new Row(true);
                
                $box = new Box(8);
                $input = [];
                $input[0] = new Uneditable('declined-by-nif');
                $input[0]->setSpan(2);
                $input[0]->setValue($disposal->getDeclinedBy()->getNif());
                
                $input[1] = new Uneditable('declined-by-name');
                $input[1]->setSpan(4);
                $input[1]->setValue($disposal->getDeclinedBy()->getName());
                
                $form->buildField('Recusado Por', $input, false, $box);
                $form->unregister($input[0]);
                $form->unregister($input[1]);
                $row->append($box);
                
                $box = new Box(3);
                $input = new Uneditable('declined-at');
                $input->setSpan(2);
                $input->setValue($disposal->getDeclinedAt()->format('d/m/Y H:i:s'));
                $form->buildField('Recusado em', $input, false, $box);
                $form->unregister($input);
                $row->append($box);
                
                $footer2->append($row);
                
                $input = new Uneditable('declined-jusfify');
                $input->setSpan(8);
                $input->setValue(nl2br($disposal->getJustify()));
                $form->buildField('Motivo da Recusa', $input, false, $footer2);
                $form->unregister($input);
            }
            
            if ($disposal->getForwardedAt() ) {
                $row = new Row(true);
                
                $box = new Box(8);
                $input = [];
                $input[0] = new Uneditable('forwarded-by-nif');
                $input[0]->setSpan(2);
                $input[0]->setValue($disposal->getForwardedBy()->getNif());
                
                $input[1] = new Uneditable('forwarded-by-name');
                $input[1]->setSpan(4);
                $input[1]->setValue($disposal->getForwardedBy()->getName());
                
                $form->buildField('Encaminhado Por', $input, false, $box);
                $form->unregister($input[0]);
                $form->unregister($input[1]);
                $row->append($box);
                
                $box = new Box(3);
                $input = new Uneditable('forwarded-at');
                $input->setSpan(2);
                $input->setValue($disposal->getForwardedAt()->format('d/m/Y H:i:s'));
                $form->buildField('Encaminhado em', $input, false, $box);
                $form->unregister($input);
                $row->append($box);
                
                $footer2->append($row);
            }
            
        } else {
            $row = new Row(true);
            
            $box = new Box(8);
            $input = [];
            $input[0] = new Uneditable('forwarded-by-nif');
            $input[0]->setSpan(2);
            $input[0]->setValue($disposal->getForwardedBy()->getNif());
            
            $input[1] = new Uneditable('forwarded-by-name');
            $input[1]->setSpan(4);
            $input[1]->setValue($disposal->getForwardedBy()->getName());
            
            $form->buildField('Encaminhado Por', $input, false, $box);
            $form->unregister($input[0]);
            $form->unregister($input[1]);
            $row->append($box);
            
            $box = new Box(3);
            $input = new Uneditable('forwarded-at');
            $input->setSpan(2);
            $input->setValue($disposal->getForwardedAt()->format('d/m/Y H:i:s'));
            $form->buildField('Encaminhado em', $input, false, $box);
            $form->unregister($input);
            $row->append($box);
            
            $footer2->append($row);
                
        }
        
        $table->setFooter(new Row(true, [$footer2, $footer1]));
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