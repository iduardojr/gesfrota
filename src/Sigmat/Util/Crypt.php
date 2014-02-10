<?php
namespace Sigmat\Util;

/**
 * Classe que criptografa e descriptografa uma string
 */
class Crypt
{
	/**
	 * @desc	Chave para codificação/decodificação
	 * @var 	string
	 */
	private static $KEY;
	
	/**
	 * @desc	Atribui uma chave para codificação/decodificação
	 * @param 	string $key
	 */
	public static function setKey($key)
	{
	    self::$KEY = $key;
	}
	
    /**
     * @desc	Criptografa uma string
     * @param 	string $str
     * @return 	string
     */   
	final public static function encode($str)
	{
	   $str .= "\x13";
	   $n = strlen($str);
	   if ($n % 16) $str .= str_repeat("\0", 16 - ($n % 16));
	   $i = 0;
	   $iv_len = 16;
	   $enc_text = '';
	   while ($iv_len-- > 0) 
	   {
	      $enc_text .= chr(mt_rand() & 0xff);
	   }
	   $iv = substr(self::$KEY ^ $enc_text, 0, 512);
	   while ($i < $n) {
	      $block = substr($str, $i, 16) ^ pack('H*', md5($iv));
	      $enc_text .= $block;
	      $iv = substr($block . $iv, 0, 512) ^ self::$KEY;
	      $i += 16;
	   }
	   return base64_encode($enc_text);
	}
	
	/**
	 * @desc	Descriptografa um string
	 * @param 	string $str
	 * @return 	string
	 */
	final public static function decode($str)
	{
	   $str = base64_decode($str);
	   $n = strlen($str);
	   $i = 16;
	   $plain_text = '';
	   $iv = substr(self::$KEY ^ substr($str, 0, 16), 0, 512);
	   while ($i < $n) {
	      $block = substr($str, $i, 16);
	      $plain_text .= $block ^ pack('H*', md5($iv));
	      $iv = substr($block . $iv, 0, 512) ^ self::$KEY;
	      $i += 16;
	   }
	   return preg_replace('/\x13\x00*$/', '', $plain_text);
	}
}
?>