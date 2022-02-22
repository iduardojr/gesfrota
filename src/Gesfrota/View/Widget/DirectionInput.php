<?php
namespace Gesfrota\View\Widget;

interface DirectionInput {
	
	/**
	 * @param Direction $diretion
	 */
	public function setDirection(Direction $direction);
	
	/**
	 * @return Direction
	 */
	public function getDirection();
}

