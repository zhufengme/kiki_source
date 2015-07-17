<?php

class kkcrypt {

    final public static function aes_cbc_encrypt($text,$key,$base64=true,$iv=null){
        if($iv===null){
            $iv = str_pad("\0",16);
        }
        if(strlen($key) % 16 !=0){
            $key=md5($key);
        }
        if(strlen($key)>32){
            $key=md5($key);
        }


        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_CBC, $iv);

        if($base64){
            return base64_encode($encrypted);
        }else{
            return $encrypted;
        }
    }

    final public static function aes_cbc_decrypt($encrypt_content,$key,$base64=true,$iv=null){
        if($iv===null){
            $iv = str_pad("\0",16);
        }
        if(strlen($key) % 16 !=0){
            $key=md5($key);
        }
        if(strlen($key)>32){
            $key=md5($key);
        }

        if($base64){
            $encrypted = base64_decode($encrypt_content);
        }else{
            $encrypted = $encrypt_content;
        }

        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encrypted, MCRYPT_MODE_CBC, $iv);

        return $decrypted;
    }
}

