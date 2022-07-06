<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Import;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\BuilderTable;
use Gesfrota\View\Widget\EntityDatasource;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Layout\Row;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Pagination\Pagination;
use Gesfrota\Model\Domain\ImportItem;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Form\Controls\Output;

class ImportPreProcessForm extends AbstractForm {
    
    /**
     * @var BuilderTable
     */
    protected $table;
    
    /**
     * @var Pagination
     */
    protected $pagination;
	
	/**
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Action $transform
	 * @param Action $dismiss
	 * @param Import $import
	 */
    public function __construct(Action $submit, Action $cancel, Action $transform, Action $dismiss, Import $import ) {
        $this->buildPanel('Minha Frota', 'Transformar Importação');
		$form = $this->buildForm('import-preprocess-form');
		$fieldset = new Fieldset('Dados Pré-processados <small>'. $import->getDescription(). '</small>');
		
		$this->panel->remove($this->alert);
		$fieldset->append($this->alert);
		
		$this->table = new BuilderTable('import-items-table');
		$this->table->setContextRow(function(ImportItem $item) {
		    return $item->getStatus() === null ? null : ($item->getStatus() ? 'success' : 'error');
		});
		    
	    $header = $import->getHeader();
	    unset($header[0]);
	    $this->table->buildColumnAction('transform', new Icon('icon-cog'), $transform, null, function (Button $btn, ImportItem $item) {
	        $btn->setDisabled($item->getImport()->getFinished() && $item->getStatus() === false);
	    });
        $this->table->buildColumnAction('dismiss', new Icon('icon-remove-sign'), $dismiss, null, function(Button $btn, ImportItem $item) {
            $btn->setDisabled($item->getStatus() !== null);
        });
        foreach ($header as $index => $head) {
            $this->table->buildColumnText('data', $head, null, null, null, function ($data) use ($index) {
                return $data[$index];
            });
        }
		$this->pagination = $this->table->buildPagination(clone $submit);
		$this->table->setPagination(null);
		
		$fieldset->append(new Row(true, [new Box(12, new Panel($this->table, 'import-preprocess-container'))]));
		$fieldset->append($this->pagination);
		
		$tab = new Tabbable('import-tabs');
		$tab->setPlacement(Tabbable::Left);
		
		$link = new NavLink('Upload do Arquivo');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$tab->addItem(new NavLink('Pré-processamento'), null, new TabPane($fieldset));
		
		$form->append($tab);
		
		$confirm = new Modal('modal-import-finish-confirm', new Title('Confirme', 3));
		$confirm->setBody(new Paragraph('Você tem certeza que quer finalizar essa importação?'));
		$confirm->setWidth(380);
		$confirm->addButton(new Button('Ok', new TgFormSubmit($submit, $form), Button::Primary));
		$confirm->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($confirm);

		$form->buildButton('submit', 'Finalizar', new TgModalOpen($confirm))->setDisabled(($import->getFinished()));
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @param EntityDatasource $datasource
	 */
	public function setDatasource( EntityDatasource $datasource ) {
	    $this->table->setDataSource($datasource);
	    $this->pagination->setPaginator($datasource);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Import $object ) {

	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Import $object ) {
	    foreach ($object->getItems() as $item) {
	        if ($item->getStatus() === null) {
	            $item->setReference(null);
	        }
	    }
	    $object->toFinish();
	}
	
}
?>