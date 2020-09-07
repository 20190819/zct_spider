<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\RandomUserAgent;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use QL\QueryList;

class Login extends Command implements Spider
{
    use RandomUserAgent;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zct:login';
    public $conf;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->conf = config('zct');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始登陆');
        $jar = new CookieJar();
        $response = QueryList::post($this->url(),$this->params(),[
            'headers'=>[
                'user-agent'=>$this->randomUserAgent($this->conf),
            ],
            'cookies' => $jar
        ])->getHtml();
        // 响应的cookie
        $cookieArr = $jar->toArray();
        file_put_contents(storage_path('cookies.json'),json_encode($cookieArr,256));
        // 日志
        Log::channel('zct')->debug('response',json_decode($response,true));
        Log::channel('zct')->debug('cookies',$cookieArr);
    }

    public function url(): string
    {
        return Arr::get($this->conf, 'domain') . '/login/c_loginsave.html';
    }

    public function params(): array
    {
        return [
            'username' => (int)trim(Arr::get($this->conf, 'login.username')),
            'password' => (int)Arr::get($this->conf, 'login.password'),
            'loginname' => 0,
            'authcode'=>null,
        ];
    }

    public function rules(): array
    {
        // TODO: Implement rules() method.
    }

    public function range(): string
    {
        // TODO: Implement range() method.
    }
}
