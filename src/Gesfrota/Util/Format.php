<?php

namespace Gesfrota\Util;

class Format {
	
	public static function code($number, $digits) {
		$multiplier = $digits - strlen((string) $number);
		return str_repeat('0', $multiplier > 0 ? $multiplier : 0) . $number;
	}
	
	public static function CNPJ($nif) {
	    $multiplier = 14-strlen($nif);
	    $nif = str_repeat('0', $multiplier) . $nif;
	    return  preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $nif);
	}
}

?>