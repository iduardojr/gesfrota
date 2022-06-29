<?php
namespace Gesfrota\View;

use Gesfrota\Model\Sys\Import;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Pattern\Upload;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\XFileBox;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;

class ImportUploadForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 */
	public function __construct( Action $submit, Action $cancel ) {
		$this->buildPanel('Sistema', 'Gerenciar Importações');
		$form = $this->buildForm('import-upload-form');
		
		$fieldset = new Fieldset('Upload');
		
		$input = new TextBox('desc');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->setValue('Importação feita na ' . ucfirst(utf8_encode(strftime('%A, %d %B %G %T', strtotime('now')))));
		$form->buildField('Descrição', $input, null, $fieldset);
		
		$input = new XFileBox('file');
		$input->setPlaceholder('Escolha um arquivo .csv');
		$input->setPattern(new Upload(['text/csv' => 'csv'], 'Informe um arquivo .csv'));
		$input->setSpan(3);
		$form->buildField(null, $input, null, $fieldset);
		
		$tab = new Tabbable('import-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Upload'), null, new TabPane($fieldset));
		
		$link = new NavLink('Pré-processamento');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$link = new NavLink('Transformação');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$form->append($tab);

		$form->buildButton('submit', 'Executar', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Import $object ) {
		$data['desc'] = $object->getDescription();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Import $object ) {
		$data = $this->component->getData();
		$object->setDescription($data['desc']);
		$fileName = basename($data['file']['name']);
		if ( ! move_uploaded_file($data['file']['tmp_name'], Import::getDirRoot() . $fileName) ) {
		    throw new \ErrorException('unable to move upload file to target Directory');
		}
		$object->setFileName($fileName);
	}

}
?>