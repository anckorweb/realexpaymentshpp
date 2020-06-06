<?php
/**

 * NOTICE OF LICENSE

 *

 * This file is licenced under the Software License Agreement.

 * With the purchase or the installation of the software in your application

 * you accept the licence agreement.

 *

 * You must not modify, adapt or create derivative works of this source code

 *

 *  @author    Coccinet

 *  @copyright 2017 Coccinet

 *  @license   LICENSE.txt

 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * encode & decode data
 */

class CryptData
{
    private $maCleDeCryptage=_COOKIE_KEY_;
    

    public function crypter($maChaineACrypter)
    {
        if ($this->maCleDeCryptage == "") {
            $this->maCleDeCryptage = "default";
        }
        $f_maCleDeCryptage = md5($this->maCleDeCryptage);
        $letter = -1;
        $newstr = '';
        $strlen = Tools::strlen($maChaineACrypter);
        for ($i = 0; $i < $strlen; $i++) {
            $letter++;
            if ($letter > 31) {
                $letter = 0;
            }
            $neword = ord($maChaineACrypter{$i}) + ord($f_maCleDeCryptage{$letter});
            if ($neword > 255) {
                $neword -= 256;
            }
            $newstr .= chr($neword);
        }
        return base64_encode($newstr);
    }

    public function decrypter($maChaineCrypter)
    {
        if ($this->maCleDeCryptage == "") {
            $this->maCleDeCryptage = "default";
        }
        $f_maCleDeCryptage = md5($this->maCleDeCryptage);
        $letter = -1;
        $newstr = '';
        $maChaineCrypter = base64_decode($maChaineCrypter);
        $strlen = Tools::strlen(utf8_encode($maChaineCrypter));
        // var_dump(utf8_encode($maChaineCrypter),$strlen);die;
        for ($i = 0; $i < $strlen; $i++) {
            $letter++;
            if ($letter > 31) {
                $letter = 0;
            }
            $neword = ord($maChaineCrypter{$i}) - ord($f_maCleDeCryptage{$letter});
            if ($neword < 1) {
                $neword += 256;
            }
            $newstr .= chr($neword);
        }
        return $newstr;
    }
}
