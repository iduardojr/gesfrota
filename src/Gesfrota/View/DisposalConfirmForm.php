<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Disposal;
use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\ArrayDatasource;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Validate\Measure\Max;
use PHPBootstrap\Validate\Measure\Ruler\RulerLength;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgWindows;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Form\Controls\TextArea;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Table\ColumnText;

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
	 * @param Action $confirm
	 * @param Action $decline
	 */
	public function __construct( Disposal $disposal, Action $cancel, Action $printAsset, Action $print, Action $confirm = null, Action $decline = null ) {
    	$this->buildPanel('Minha Frota', 'Gerenciar Disposições para Alienação');
		$form = $this->buildForm('disposal-confirm-form');
		
		
		$allowed = Disposal::getStatusAllowed();
		$status = $disposal->getStatus();
		switch ( $status ) {
			case Disposal::REQUESTED:
				$label = '<span class="label label-warning">' . $allowed[$status] . '</span>';
				break;
				
			case Disposal::CONFIRMED:
				$label = '<span class="label label-success">' . $allowed[$status] . '</span>';
				break;
				
			case Disposal::DECLINED:
				$label = '<span class="label label-important">' . $allowed[$status] . '</span>';
				break;
		}
		
		$general = new Fieldset('Disposição #' . $disposal->getCode() . ' <small>' . $disposal->getDescription() . '</small> ' . $label);
		
		$this->flashMessage = $this->panel->getByName('flash-message');
		$this->panel->remove($this->flashMessage);
		$general->append($this->flashMessage);
		
		
		if($status == Disposal::DECLINED) {
			$input = new Output('jusfify');
			$input->setValue(nl2br($disposal->getJustify()));
			$form->buildField('Motivo da Recusa', $input, false, $general);
			$form->unregister($input);
		}
		
		$table = new BuilderTable('disposal-items-table');
		
		$table->buildColumnText('code', '#', null, 80);
		$table->buildColumnText('description', 'Ativo', null, 500, ColumnText::Left);
		$table->buildColumnText('classification', 'Classificação', null, 75, null, function ( $label ) {
		    if ($label > 0) {
		        return DisposalItem::getClassificationAllowed()[$label];
		    }
		    return '-';
		});
		    
	    $table->buildColumnText('rating', null, null, 100);
		        
	    $table->buildColumnText('value', 'Valor', null, null, null, function ( $label ) {
            if ($label > 0) {
                return 'R$ ' . number_format($label, 2, ',', '.');
            }
            return '-';
        });
		            
        $table->buildColumnText('debit', 'Débitos', null, null, null, function ( $label ) {
            if ($label > 0) {
                return 'R$ ' . number_format($label, 2, ',', '.');
            }
            return '-';
        });
        
        $modalView = new Modal('disposal-survey-print', new Title('Avaliação do Ativo', 3));
        
        $table->buildColumnAction('print', new Icon('icon-print'), new TgWindows($printAsset, 1024, 720));
        $table->setDataSource(new ArrayDatasource($disposal->getAllAssets(), 'id'));
        
        $foot = new Box(['offset' => 4]);
        $input = new Output('requester-unit');
        $input->setValue($disposal->getRequesterUnit()->getAcronym());
        $form->buildField('Disponibilizado Por', $input, false, $foot);
        $form->unregister($input);
        
        $input = new Output('requester-by');
        $input->setValue($disposal->getRequestedBy()->getNif() . ' ' . $disposal->getRequestedBy()->getName());
        $form->buildField('Avaliador Responsável', $input, false, $foot);
        $form->unregister($input);
        
        $input = new Output('requester-at');
        $input->setValue($disposal->getRequestedAt()->format('d/m/Y H:m:i'));
        $form->buildField('Encaminhado em', $input, false, $foot);
        $form->unregister($input);
        
        if ($disposal->getStatus() == Disposal::CONFIRMED) {
        	$input = new Output('confirmed-at');
        	$input->setValue($disposal->getConfirmedAt()->format('d/m/Y H:m:i'));
        	$form->buildField('Confirmado em', $input, null, $foot);
        	$form->unregister($input);
        }
        
        if ($disposal->getStatus() == Disposal::DECLINED) {
        	$input = new Output('declined-at');
        	$input->setValue($disposal->getDeclinedAt()->format('d/m/Y H:m:i'));
        	$form->buildField('Recusada em', $input, null, $foot);
        	$form->unregister($input);
        }
        
        $general->append($table);
        $general->append($foot);
        $general->append($modalView);
        
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
        $style = true;
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
			
			$form->buildButton('confirm', [new Icon('icon-thumbs-up', true), 'Confirmar Disposição'], new TgFormSubmit($confirm, $form, false), Button::Primary);
			$form->buildButton('decline', [new Icon('icon-thumbs-down', true),'Recusar Disposição'], new TgModalOpen($modalDecline), Button::Danger);
			$style = false;
		}
		$form->buildButton('print', [new Icon('icon-print', $style), 'Imprimir Disposição'], new TgWindows($print, 1024, 762), $style ? Button::Primary : null);
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