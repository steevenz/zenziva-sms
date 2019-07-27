# Zenziva SMS

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/steevenz/zenziva-sms/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/steevenz/zenziva-sms/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/steevenz/zenziva-sms/badges/build.png?b=master)](https://scrutinizer-ci.com/g/steevenz/zenziva-sms/build-status/master)
[![Latest Stable Version](https://poser.pugx.org/steevenz/zenziva-sms/v/stable)](https://packagist.org/packages/steevenz/zenziva-sms)
[![Total Downloads](https://poser.pugx.org/steevenz/zenziva-sms/downloads)](https://packagist.org/packages/steevenz/zenziva-sms)
[![License](https://poser.pugx.org/steevenz/zenziva-sms/license)](https://packagist.org/packages/steevenz/zenziva-sms)

Zenziva SMS API PHP Class Library berfungsi untuk melakukan request API pengiriman SMS menggunakan [Zenziva](http://www.zenziva.id/).

Instalasi
---------
Cara terbaik untuk melakukan instalasi library ini adalah dengan menggunakan [Composer](https://getcomposer.org)
```
composer require steevenz/zenziva-sms
```

Penggunaan
----------
```php
use Steevenz\ZenzivaSms;

/*
 * --------------------------------------------------------------
 * Inisiasi Class ZenzivaSms
 *
 * @param string Username
 * @param string API Key
 * --------------------------------------------------------------
 */
 $zenzivaSms = new ZenzivaSms([
    'userkey' => 'USERKEY_ANDA',
    'passkey' => 'PASSKEY_ANDA'
 ]);

/*
 * --------------------------------------------------------------
 * Melakukan send sms
 *
 * @param string Phone Number
 * @param string Text
 *
 * @return object|bool
 * --------------------------------------------------------------
 */
 // send message
 $result = $zenzivaSms->send('082123456789','Testing Zenziva SMS API');

 // send one time password (OTP)
 $result = $zenzivaSms->sendOtp('082123456789','KODE123');
```

Ide, Kritik dan Saran
---------------------
Jika anda memiliki ide, kritik ataupun saran, anda dapat mengirimkan email ke [steevenz@stevenz.com](mailto:steevenz@steevenz.com). 
Anda juga dapat mengunjungi situs pribadi saya di [steevenz.com](http://steevenz.com)

Bugs and Issues
---------------
Jika anda menemukan bugs atau issue, anda dapat mempostingnya di [Github Issues](http://github.com/steevenz/zenziva-sms/issues).

Requirements
------------
- PHP 7.2+
- [Composer](https://getcomposer.org)
- [O2System Curl](http://github.com/o2system/curl)