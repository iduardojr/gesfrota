<?php
namespace Gesfrota\Model\Domain;

/**
 * Importação de Transações
 * 
 * @Entity
 */
abstract class ImportTransaction extends Import {

    
    /**
     * @ManyToOne(targetEntity="ServiceProvider")
     * @JoinColumn(name="service_provider_id", referencedColumnName="id")
     * @var ServiceProvider
     */
    protected $serviceProvider;
    
    /**
     * @Column(name="date_initial", type="date")
     * @var \DateTime
     */
    protected $dateInitial;
    
    /**
     * @Column(name="date_final", type="date")
     * @var \DateTime;
     */
    protected $dateFinal;
    
    /**
     * @return ServiceProvider
     */
    public function getServiceProvider() {
        return $this->serviceProvider;
    }

    /**
     * @return \DateTime
     */
    public function getDateInitial() {
        return $this->dateInitial;
    }

    /**
     * @return \DateTime;
     */
    public function getDateFinal() {
        return $this->dateFinal;
    }
    
    /**
     * @return \DateTime[]
     */
    public function getDatePeriod() {
        return [$this->dateInitial, $this->dateFinal];
    }

    /**
     * @param ServiceProvider $provider
     */
    public function setServiceProvider(ServiceProvider $provider){
        $this->serviceProvider = $provider;
    }
    
    /**
     * @param \DateTime $initial
     * @param \DateTime $final
     */
    public function setDatePeriod(\DateTime $initial, \DateTime $final) {
        $this->dateInitial = $initial;
        $this->dateFinal = $final;
    }
    
    
}
?>