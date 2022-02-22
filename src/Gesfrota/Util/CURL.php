<?php
namespace Gesfrota\Util;

/**
 * Classe que conecta a uma url
 */
class CURL {
    
    /**
     * Conecta e obtem os dados de uma url
     * 
     * @param string $url
     * @param array $post
     * @return string	
     * @throws \Exception
     */
    public static function connect( $url, array $post = null ) {
        $rs = curl_init();
        curl_setopt($rs, CURLOPT_URL, $url);
        curl_setopt($rs, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($rs, CURLOPT_TIMEOUT, 15);
        if ( empty($post) == false ) {
            curl_setopt($rs, CURLOPT_POST, 1);
            curl_setopt($rs, CURLOPT_POSTFIELDS, $post);
        }
        $content = curl_exec($rs);
        if ( $content === false ) {
            throw new \Exception('[' . curl_errno($rs) . '] ' . curl_error($rs) );
        }
        curl_close($rs);
        return $content;
    }
    
}
?>