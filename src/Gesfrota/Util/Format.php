<?php

namespace Gesfrota\Util;

class Format {
	
	public static function code($number, $digits) {
		return str_repeat('0', $digits - strlen((string) $number)) . $number;
	}
}

?>