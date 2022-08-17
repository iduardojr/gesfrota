<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\ImportFleet;
use Gesfrota\Model\Domain\ImportFleetItem;
use Gesfrota\Model\Domain\ImportTransaction;
use Gesfrota\Model\Domain\ImportTransactionItem;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\BuilderTable;
use Gesfrota\View\Widget\EntityDatasource;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Pagination\Pagination;

class ImportTransactionItemsList extends AbstractForm {
    
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
	 * @param Action $remove
	 * @param Action $download
	 * @param Action $cancel
	 * @param ImportTransaction $import
	 */
    public function __construct(Action $submit, Action $remove, Action $download, Action $cancel, ImportTransaction $import ) {
        $this->buildPanel('Entidades Externas', 'Importação de Transações de Abastecimento');
		$form = $this->buildForm('import-transaction-items-list');
		
		$this->table = new BuilderTable('import-items-table');
		    
	    $header = $import->getHeader();
        foreach ($header as $index => $head) {
            $this->table->buildColumnText('data', $head, null, null, null, function ($data, ImportTransactionItem $item) use ($index) {
                $data = $item->getData();
                return ! empty($data[$index]) ? str_replace(['&lt;','&gt;'], [' <small>&lt;','&gt;</small>'], htmlentities($data[$index])) : '<code>null</code>';
            });
        }
		$this->pagination = $this->table->buildPagination(clone $submit);
		$this->table->setPagination(null);
		
		$form->append(new Panel($this->table, 'import-items-table-container'));
		$form->append($this->pagination);
		
		$form->buildButton('download', 'Baixar Arquivo', $download);
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
	public function extract( ImportFleet $object ) {

	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( ImportFleetItem $object ) {
	    foreach ($object->getItems() as $item) {
	        if ($item->getStatus() === null) {
	            $item->setReference(null);
	        }
	    }
	    $object->toFinish();
	}
	
}
?>