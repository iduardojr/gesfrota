<?php
namespace Gesfrota\Model\Domain;

interface Engine {
    /**
     * Gasolina
     * @var integer
     */
    const GASOLINE = 1;
    
    /**
     * Etanol
     * @var integer
     */
    const ETHANOL = 2;
    
    /**
     * Gasolina e Etanol
     * @var integer
     */
    const FLEX = 3;
    
    /**
     * Diesel
     * @var integer
     */
    const DIESEL = 4;
    
}