<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\RandomUserAgent;
use App\Exceptions\HttpException;
use App\Jobs\ZctDetail;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use QL\QueryList;

class ListPage extends Command implements Spider
{
    use RandomUserAgent;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zct:list {page?} {cookies?}';
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


    public function handle()
    {
        $page = $this->argument('page') ?? 1;
        $cookieArr2 = $this->argument('cookies') ?? null;
        if (empty($cookieArr2)) {
            $cookieArr2 = file_get_contents(storage_path('cookies.json'));
            $cookieArr2 = json_decode($cookieArr2, true);
        }
        $jar2 = new CookieJar(false, $cookieArr2);

        $list = QueryList::get($this->url($page), [], [
            'headers' => [
                'user-agent' => $this->randomUserAgent($this->conf),
            ],
            'cookies' => $jar2
        ])->range($this->range())->rules($this->rules())->encoding('UTF-8', 'GB2312')->removeHead()->queryData();
//        Log::channel('zct')->debug('list_arr', $list);
        foreach ($list as $item) {
            // 正则匹配ID
            preg_match('/(\d+)/', Arr::get($item, 'detail_url'), $res);
            $id = (int)$res[0] ?? null;
            $city = Arr::get($item, 'city');
            // 队列任务
            dispatch(new ZctDetail((int)$id, $city, $cookieArr2));
        }
        Log::channel('zct')->info("爬取{$page}页完成");
    }

    public function url(int $page): string
    {
        return Arr::get($this->conf, 'domain') . "/once/page_{$page}.html";
    }

    public function params(): array
    {
        // TODO: Implement params() method.
    }


    public function range(): string
    {
        return '.recruit_micro_list_once>ul>li';
    }

    public function rules(): array
    {
        return [
            'detail_url' => ['a', 'href'],
            'city' => ['.recruit_list_detail_city', 'text'],
        ];
    }

}
