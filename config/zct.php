<?php
/**
 * Created by phpstorm.
 * User: yangliang
 * Date: 2020/9/4 0004
 * Time: 10:17
 */

return [
    'domain' => env('ZCT_DOMAIN', 'https://job.zgzsrc.com'),
    'login' => [
        'username' => env('ZCT_USERNAME', '18224009904'),
        'password' => env('ZCT_PASSWORD', '123456')
    ],
    'user_agent' => [
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.163 Safari/535.1',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0) Gecko/20100101 Firefox/6.0',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50',
        'Opera/9.80 (Windows NT 6.1; U; zh-cn) Presto/2.9.168 Version/11.50'
    ],
];
