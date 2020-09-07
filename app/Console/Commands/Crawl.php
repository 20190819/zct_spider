<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\RandomUserAgent;
use App\Exceptions\HttpException;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use QL\QueryList;

class Crawl extends Command
{
    use RandomUserAgent;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zct:crawl';
    public $conf;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬虫入口';

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
     * @throws HttpException
     */
    public function handle()
    {
        $this->checkLogin();
        $cookieArr2 = file_get_contents(storage_path('cookies.json'));
        $cookieArr2 = json_decode($cookieArr2, true);
        $jar2 = new CookieJar(false, $cookieArr2);

        $html = QueryList::get($this->url(1), [], [
            'headers' => [
                'user-agent' => $this->randomUserAgent($this->conf),
            ],
            'cookies' => $jar2
        ])->encoding('UTF-8', 'GB2312')->removeHead()->getHtml();
//        Log::channel('zct')->debug($html);
        $ql = QueryList::html($html);
        // 寻找最大页数
        $max_page = (int)$ql->find('.pages>a')->eq(5)->text();
        // 开始爬虫
        for ($i = 1; $i <= $max_page; $i++) {
            $this->info("开始爬取{$i}页");
            Artisan::call('zct:list', ['page' => $i, 'cookies' => $cookieArr2]);
            sleep(3);
        }
        dd('ok  '.now());
    }

    public function url(int $page): string
    {
        return Arr::get($this->conf, 'domain') . "/once/page_{$page}.html";
    }


    public function checkLogin()
    {
        $bool = file_exists(storage_path('cookies.json'));
        if ($bool) {
            $cookieArr2 = file_get_contents(storage_path('cookies.json'));
            $cookieArr2 = json_decode($cookieArr2, true);
            $arr = Arr::where($cookieArr2, function ($item) {
                return Arr::has($item, 'Expires');
            });
            // cookie过期啦
            if ($arr[0]['Expires'] <= now()->timestamp) {
                Artisan::call('zct:login');
            }
        } else {
            Artisan::call('zct:login');
        }
    }
}
