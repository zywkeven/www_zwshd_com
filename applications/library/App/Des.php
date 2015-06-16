<?php 
class App_Des {
    
    public static $key ='84fae343';
  
    //加密
    public static function encrypt($str){
        $size = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_CBC );
        $str = self::pkcs5Pad ( $str, $size );
        $data = mcrypt_encrypt(MCRYPT_DES, self::$key, $str, MCRYPT_MODE_CBC, self::$key);
        $data = strtolower(bin2hex($data));
        return $data;
    }
  
    //解密
    public static function decrypt($str) {
        $str = self::hex2bin( strtolower($str));
        $str = mcrypt_decrypt(MCRYPT_DES, self::$key, $str, MCRYPT_MODE_CBC, self::$key);
        $str = self::pkcs5Unpad( $str );
        return $str;
    }
  
    private static function hex2bin($hexData){
        $binData = "";
        for($i = 0; $i < strlen ( $hexData ); $i += 2){
            $binData .= chr(hexdec(substr($hexData, $i, 2)));
        }
        return $binData;
    }
  
    private static function pkcs5Pad($text, $blocksize){
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }
  
    private static function pkcs5Unpad($text){
        $pad = ord ( $text {strlen ( $text ) - 1} );
        if ($pad > strlen ( $text ))
            return false;
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
            return false;
        return substr ( $text, 0, - 1 * $pad );
    }
}