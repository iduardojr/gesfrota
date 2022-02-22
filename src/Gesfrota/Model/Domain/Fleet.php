<?php
namespace Gesfrota\Model\Domain;

interface Fleet {
    
    /**
     * Própria
     * @var integer
     */
    const OWN = 1;
    
    /**
     * Locada
     * @var integer
     */
    const RENTED = 2;
    
    /**
     * Cedida
     * @var integer
     */
    const ASSIGNED = 4;
    
    /**
     * Acautelada
     * @var integer
     */
    const GUARDED = 8;
    
}