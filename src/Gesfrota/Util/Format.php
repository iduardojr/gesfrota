<?php

namespace Gesfrota\Util;

class Format {
	
	public static function code($number, $digits) {
		$multiplier = $digits - strlen((string) $number);
		return str_repeat('0', $multiplier > 0 ? $multiplier : 0) . $number;
	}
}

?>