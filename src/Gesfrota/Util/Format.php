<?php

namespace Gesfrota\Util;

class Format {
	
	public static function code($number, $digits) {
		$multiplier = $digits - strlen((string) $number);
		return str_repeat('0', $multiplier > 0 ? $multiplier : 0) . $number;
	}
	
	public static function CNPJ($nif) {
	    $multiplier = 14-strlen($nif);
	    $nif = str_repeat('0', $multiplier > 0 ? $multiplier : 0) . $nif;
	    return  preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $nif);
	}
	
	public static function CPF($nif) {
	    $multiplier = 11-strlen($nif);
	    $nif = str_repeat('0', $multiplier > 0 ? $multiplier : 0) . $nif;
	    return  preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $nif);
	}
	
	public static function byte($bytes, $precision = 1) {
	    $bytes = floatval($bytes);
	    $multiples = [
	        ["UNIT" => "TB", "VALUE" => pow(1024, 4)],
	        ["UNIT" => "GB", "VALUE" => pow(1024, 3)],
	        ["UNIT" => "MB", "VALUE" => pow(1024, 2)],
	        ["UNIT" => "KB", "VALUE" => 1024],
	        ["UNIT" => "B ", "VALUE" => 1],
	    ];
	    
	    foreach($multiples as $multiple) {
	        if($bytes >= $multiple["VALUE"]) {
	            $result = $bytes / $multiple["VALUE"];
	            $result = strval(round($result, $precision))." ".$multiple["UNIT"];
	            break;
	        }
	    }
	    return $result;
	}
	
	public static function financial($number, $precision = 2, $abbr = false, $currency = 'R$ ') {
	    $number = floatval($number);
	    $multiples = [
	        ["ABBR" => "Tri", "UNIT" => "Trilhões", "VALUE" => pow(1000, 4)],
	        ["ABBR" => " Bi", "UNIT" => "Bilhões",  "VALUE" => pow(1000, 3)],
	        ["ABBR" => " Mi", "UNIT" => "Milhões",  "VALUE" => pow(1000, 2)],
	        ["ABBR" => "Mil", "UNIT" => "Mil",      "VALUE" => pow(1000, 1)],
	    ];
	    $result = $currency . number_format(round($number, $precision), $precision, ',', '');
	    foreach($multiples as $multiple) {
	        if($number >= $multiple["VALUE"]) {
	            $result = $number / $multiple["VALUE"];
	            $result = $currency . number_format(round($result, $precision), $precision, ',', '')." ".$multiple[$abbr ? "ABBR" : "UNIT"];
	            break;
	        }
	    }
	    return $result;
	}
	
	public static function numeric($number, $precision = 2) {
	    $number = floatval($number);
	    $multiples = [
	        ["UNIT" => "T", "VALUE" => pow(1000, 4)],
	        ["UNIT" => "G", "VALUE" => pow(1000, 3)],
	        ["UNIT" => "M", "VALUE" => pow(1000, 2)],
	        ["UNIT" => "K", "VALUE" => pow(1000, 1)],
	    ];
	    $result = number_format(round($number, $precision), $precision, ',', '');
	    foreach($multiples as $multiple) {
	        if($number >= $multiple["VALUE"]) {
	            $result = $number / $multiple["VALUE"];
	            $result = number_format(round($result, $precision), $precision, ',', '')." ".$multiple["UNIT"];
	            break;
	        }
	    }
	    return $result;
	}
}
?>