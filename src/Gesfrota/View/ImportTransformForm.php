<?php
namespace Gesfrota\View;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Sys\Import;
use Gesfrota\Model\Sys\ImportItem;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\ArrayDatasource;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Table\Table;
use Gesfrota\View\Widget\EntityDatasource;
use PHPBootstrap\Widget\Pagination\Pagination;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Row;

class ImportTransformForm extends AbstractForm {
    
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
	 * @param Import $import
	 * @param Agency $agency
	 */
    public function __construct(Action $submit, Action $cancel, Action $transform, Action $dismiss, Import $import, Agency $agency ) {
		$this->buildPanel('Sistema', 'Gerenciar Importações');
		$form = $this->buildForm('import-transform-form');
		$fieldset = new Fieldset('Transformação <small>'. $import->getDescription(). '</small>');
		
		$this->panel->remove($this->alert);
		$fieldset->append($this->alert);
		
		$this->table = new BuilderTable('import-items-table');
		$this->table->setContextRow(function(ImportItem $item) {
		    return $item->getStatus() === null ? null : ($item->getStatus() ? 'success' : 'error');
		});
		
		$header = $import->getHeader();
		unset($header[0]);
		$this->table->buildColumnAction('transform', new Icon('icon-cog'), $transform);
		$this->table->buildColumnAction('dismiss', new Icon('icon-remove-sign'), $dismiss, null, function(Button $btn, ImportItem $item) {
		    $btn->setDisabled($item->getStatus() !== null);
		});
		if ( $agency->isGovernment() ) {
		    $this->table->buildColumnText('agency', 'Órgão');
		}
		foreach ($header as $index => $head) {
		    $this->table->buildColumnText('data', $head, null, null, null, function ($data) use ($index) {
		        return $data[$index];
		    });
		}
		$this->pagination = $this->table->buildPagination(clone $submit);
		$this->table->setPagination(null);
		$this->pagination->setAlign(Pagination::Center);
		
		$fieldset->append(new Row(true, [new Box(12, new Panel($this->table, 'import-transform-container'))]));
		$fieldset->append($this->pagination);
		
		$tab = new Tabbable('import-tabs');
		$tab->setPlacement(Tabbable::Left);
		
		$link = new NavLink('Upload');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$link = new NavLink('Pré-processamento');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$tab->addItem(new NavLink('Transformação'), null, new TabPane($fieldset));
		
		$form->append($tab);

		$form->buildButton('submit', 'Executar', $submit);
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
	    
	}
	
}
?>