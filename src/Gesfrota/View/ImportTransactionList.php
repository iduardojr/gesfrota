<?php
namespace Gesfrota\View;

use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\DateBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalConfirm;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;
use Gesfrota\Model\Domain\ImportTransaction;
use Gesfrota\Util\Format;
use PHPBootstrap\Widget\Dropdown\Dropdown;
use PHPBootstrap\Widget\Dropdown\DropdownLink;
use Gesfrota\Model\Domain\ImportSupply;
use Gesfrota\Model\Domain\ImportMaintenance;
use PHPBootstrap\Widget\Dropdown\TgDropdown;

class ImportTransactionList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $uploadSupply
	 * @param Action $uploadMaintenance
	 * @param Action $preProcess
	 * @param Action $listItems
	 * @param Action $download
	 * @param Action $remove
	 * @param array $providers
	 */
	public function __construct( Action $filter, Action $uploadSupply, Action $uploadMaintenance, Action $preProcess, Action $listItems, Action $download, Action $remove, array $providers) {
		$this->buildPanel('Entidades Externas', 'Importar Transações de Serviço');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
	    $input = new ComboBox('provider');
	    $input->setSpan(2);
	    $input->setOptions($providers);
	    $form->buildField('Prestador de Serviço', $input);
		
		$input = new TextBox('desc');
		$input->setSpan(5);
		$form->buildField('Descrição', $input);
		
		$input = [];
		$input[1] = new DateBox('date-initial', new Date(new DateFormat('dd/mm/yyyy')));
		$input[1]->setSpan(2);
		
		$input[2] = new DateBox('date-final', new Date(new DateFormat('dd/mm/yyyy')));
		$input[2]->setSpan(2);
		$form->buildField('Período', $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$modalFilter->setWidth(750);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$drop = new Dropdown();
		$drop->addItem(new DropdownLink(ImportSupply::SERVICE_TYPE, new TgLink($uploadSupply)));
		$drop->addItem(new DropdownLink(ImportMaintenance::SERVICE_TYPE, new TgLink($uploadMaintenance)));
		
		$this->buildToolbar(array(new Button('Importar', null, Button::Primary), new Button('', new TgDropdown($drop), Button::Primary)),
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('import-transaction-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('description', 'Descrição', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('transactionType', null, null, 95, null, function($value, ImportTransaction $import) {
		    return '<span class="label label-' . ($import instanceof ImportSupply ? 'success' : 'warning') . '">' . $value . '</span>';
		});
		$table->buildColumnText('dateInitial', 'Período', clone $filter, 150, null, function($value, ImportTransaction $import) {
		    $value = $import->getDatePeriod();
		    return $value[0]->format('d/m/Y') . ' a ' . $value[1]->format('d/m/Y');
		});
	    $table->buildColumnText('serviceProvider', 'Prestador', clone $filter, 80, null);
		$table->buildColumnText('amountItems', 'Trans. Importadas', null, 50, null, function($value) {
		    return number_format($value, 0, ',', '.');
		});
		$table->buildColumnText('fileSize', null, null, 70, null, function( $bytes ) {
		    return Format::byte($bytes);
		});
		
		$confirm = new Modal('modal-remove-confirm', new Title('Confirme', 3));
		$confirm->setBody(new Paragraph('Você deseja excluir definitivamente esta Importação?'));
		$confirm->setWidth(350);
		$confirm->addButton(new Button('Ok', new TgModalConfirm(), Button::Primary));
		$confirm->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($confirm);
		
		$table->buildColumnAction('download', new Icon('icon-download-alt'), $download);
		$table->buildColumnAction('pre-process', new Icon('icon-cog'), $preProcess, null, function (Button $btn, ImportTransaction $import) {
		    $btn->setDisabled($import->getFinished());
		});
		$table->buildColumnAction('items-list', new Icon('icon-list'), $listItems);
		$table->buildColumnAction('remove', new Icon('icon-remove'), $remove, $confirm);
		
	}
	
}
?>