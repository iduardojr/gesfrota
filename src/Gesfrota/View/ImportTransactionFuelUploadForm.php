<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\ImportFleet;
use Gesfrota\Model\Domain\ImportFleetItem;
use Gesfrota\Model\Domain\ImportTransaction;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Pattern\Upload;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\XFileBox;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Misc\Well;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use Gesfrota\Model\Domain\ServiceProvider;
use Gesfrota\Model\Domain\ImportTransactionFuel;
use Gesfrota\Model\Domain\Vehicle;
use PHPBootstrap\Widget\Form\Controls\DateBox;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Format\DateTimeParser;

class ImportTransactionFuelUploadForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param Action $cancel
	 * @param array $providers
	 */
    public function __construct( Action $submit, Action $cancel, array $providers) {
        $this->buildPanel('Entidades Externas', 'Importação de Transações de Abastecimento');
		$form = $this->buildForm('import-transaction-upload-form');
		
		$fieldset = new Fieldset('Enviar Arquivo para Importação');
		
		$input = new ComboBox('provider');
		$input->setSpan(2);
		$input->setOptions($providers);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Prestador de Serviço', $input, null, $fieldset);
		
		$input = new TextBox('desc');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->setValue('Importação feita na ' . ucfirst(utf8_encode(strftime('%A, %d %B %G %T', strtotime('now')))));
		$form->buildField('Descrição', $input, null, $fieldset);
		
		$input = new XFileBox('file');
		$input->setPlaceholder('Escolha um arquivo .csv');
		$input->setPattern(new Upload(['text/csv' => 'csv'], 'Informe um arquivo .csv'));
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Arquivo', $input, null, $fieldset);
		
		$input = [];
		$input[0] = new DateBox('date-initial', new Date(new DateFormat('dd/mm/yyyy', DateTimeParser::getInstance()), 'Por favor, informe uma data no formato dd/mm/yyyy'));
		$input[0]->setSpan(2);
		$input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[0]->setPlaceholder('Data Inicial');
		
		$input[1] = new DateBox('date-final', new Date(new DateFormat('dd/mm/yyyy', DateTimeParser::getInstance()), 'Por favor, informe uma data no formato dd/mm/yyyy'));
		$input[1]->setSpan(2);
		$input[1]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input[1]->setPlaceholder('Data Final');
		$form->buildField('Período', $input, null, $fieldset);
		
		
		$text[]= '<p>O arquivo deve ter o tamanho máximo de <code>'. ini_get('upload_max_filesize') . 'B</code>
                     e os seus valores devem ser separados por <code>;</code> e delimitados por <code>"</code>.</p>';
		$text[]= '<p>A primeira linha é o cabeçalho do arquivo correspondendo ao nome dos campos 
                     e as demais linhas é um registro com os seus respectivos valores formatados da seguinte forma e sequência:</p>';
		$text[]= '<dl class="dl-horizontal">
                     <dt>Órgão</dt>                     <dd><i>alfanumérico</i></dd>
                     <dt>Data da Transação</dt>         <dd><i>aaaa-mm-dd hh:mm:ss</i></dd>   
                     <dt>Placa do Veículo</dt>          <dd>AAA9*999</dd>
                     <dt>Descrição do Veículo</dt>      <dd><i>alfanumérico</i></dd>
                     <dt>[CPF do Motorista]</dt>        <dd><i>numérico</i></dd>
                     <dt>Nome do Motorista</dt>         <dd><i>alfanumérico</i></dd>
                     <dt>[CNPJ do Estabelecimento]</dt> <dd><i>numérico</i></dd>
                     <dt>Nome do Estabelecimento</dt>   <dd><i>alfanumérico</i></dd>
                     <dt>Cidade</dt>                    <dd><i>alfanumérico</i></dd>
                     <dt>UF</dt>                        <dd>AA</dd>
                     <dt>Descrição do Item</dt>         <dd>ARLA-32 | [GASOLINA|ETANOL|DIESEL|DIESEL S-10] [COMUM|ADITIVAD*|PREMIUM]</dd>
                     <dt>Quant. do Item</dt>            <dd>999,99</dd>
                     <dt>Preço do Item</dt>             <dd>999,999</dd>
                     <dt>Valor do Item</dt>             <dd>999,99</dd>
                     <dt>Distância Percorrida</dt>      <dd><i>numérico</i></dd>
                     <dt>Rendimento do Veículo</dt>     <dd>999,99</dd>
                  </dl>';
		$text[]= '<p>As seguintes definições representam um caractere 
                     alfabético <code>A</code>, numérico <code>9</code> e alfanumérico <code>*</code>. 
                     As colunas entre <code>[]</code> são opcionais, sendo possível o arquivo ter 14 ou 16 colunas (arquivo reduzido ou expandido).</p>';

		$form->buildField(null, new Well('info', new Panel(implode('', $text))), null, $fieldset);
		
		$tab = new Tabbable('import-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Upload do Arquivo'), null, new TabPane($fieldset));
		
		$link = new NavLink('Pré-processamento');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$form->append($tab);

		$form->buildButton('submit', 'Executar', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( ImportTransaction $object ) {
		$data['desc'] = $object->getDescription();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( ImportTransaction $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setDescription($data['desc']);
		$object->setServiceProvider($em->find(ServiceProvider::getClass(), $data['provider']));
		$fileName = date('YmdHis') . '-' . uniqid() . '.csv';
		$dirRoot = DIR_ROOT . str_replace('/', DIRECTORY_SEPARATOR, ImportFleet::DIR);
		if ( ! move_uploaded_file($data['file']['tmp_name'], $dirRoot . $fileName) ) {
		    throw new \ErrorException('Unable to move upload file to target Directory');
		}
		$object->setFileName($fileName);
		$object->setFileSize($data['file']['size']);
		
		$object->setDatePeriod($data['date-initial'], $data['date-final']);
		
		$qb = $em->getRepository(ImportTransaction::class)->createQueryBuilder('u');
		$qb->select('COUNT(u)');
		$qb->where('u.serviceProvider = :provider');
		$qb->setParameter('provider', $object->getServiceProvider());
		$qb->andWhere('u.dateInitial BETWEEN :initial AND :final OR u.dateFinal BETWEEN :initial AND :final');
		$qb->setParameter('initial', $data['date-initial']);
		$qb->setParameter('final', $data['date-final']);
		$importsExist = $qb->getQuery()->getSingleScalarResult();
		if ( $importsExist ) {
		    throw new \DomainException('Há ' . $importsExist . ' importaç' . ($importsExist == 1 ? 'ão' : 'ões'). ' para <em>' . $object->getServiceProvider()->getAlias() . '</em> no mesmo período.');
		}
		$file = fopen($dirRoot . $fileName, 'r', true);
		
		if ( $line = fgetcsv($file, 0, ";") ) {
		    $object->setHeader($this->transform($line));
		}
		$object->getItems()->clear();
		if ($object->getId() == 0) {
		    $em->persist($object);
		}
		$em->flush($object);
		while ($line = fgetcsv($file, 0, ";")) {
		    $item = new ImportTransactionFuel($object, $this->transform($line));
		    $vehicle = $em->getRepository(Vehicle::getClass())->findOneBy(['plate' => $item->getVehiclePlate()]);
		    if ($vehicle instanceof Vehicle) {
		          $item->setTransactionVehicle($vehicle);
		    }
		    $em->persist($item);
		    $em->flush($item);
		    $em->detach($item);
		}
	}
	
	/**
	 * @param array $data
	 * @return array
	 */
	private function transform(array $data) {
	    foreach($data as $i => $val) {
	        $data[$i] = utf8_encode($val);
	    }
	    return $data;
	}

}
?>