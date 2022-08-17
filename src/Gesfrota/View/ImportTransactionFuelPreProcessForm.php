<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\ImportTransaction;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;

class ImportTransactionFuelPreProcessForm extends AbstractForm {
    
	
	/**
	 * @param Action $submit
	 * @param Action $remove
	 * @param Action $download
	 * @param Action $cancel
	 * @param ImportTransaction $import
	 * @param array $costCenters
	 * @param array $optAgencies
	 */
    public function __construct(Action $submit, Action $remove, Action $download, Action $cancel, ImportTransaction $import, array $costCenters, array $optAgencies ) {
        $this->buildPanel('Entidades Externas', 'Importação de Transações de Abastecimento');
		$form = $this->buildForm('import-transaction-preprocess-form');
		$fieldset = new Fieldset('Dados Pré-processados <small>'. $import->getDescription(). '</small>');
		
		$this->panel->remove($this->alert);
		$fieldset->append($this->alert);
		$this->alert->setName('alert-message');
		
		foreach ($costCenters as $key => $label) {
		    $input = new ComboBox($key);
		    $input->setOptions($optAgencies);
		    $input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		    $form->buildField($label, $input, null, $fieldset);
		}
		
		$tab = new Tabbable('import-tabs');
		$tab->setPlacement(Tabbable::Left);
		
		$link = new NavLink('Upload do Arquivo');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$tab->addItem(new NavLink('Pré-processamento'), null, new TabPane($fieldset));
		
		$form->append($tab);
		
		$confirmFinished = new Modal('modal-import-finish-confirm', new Title('Confirme', 3));
		$confirmFinished->setBody(new Paragraph('Você tem certeza que quer finalizar essa importação?'));
		$confirmFinished->setWidth(380);
		$confirmFinished->addButton(new Button('Ok', new TgFormSubmit($submit, $form), Button::Primary));
		$confirmFinished->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($confirmFinished);

		$form->buildButton('submit', 'Finalizar', new TgModalOpen($confirmFinished));
		$form->buildButton('download', 'Baixar Arquivo', $download);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( ImportTransaction $object ) {

	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( ImportTransaction $object ) {
	    
	}
	
}
?>