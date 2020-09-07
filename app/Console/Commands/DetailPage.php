<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\RandomUserAgent;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use QL\QueryList;

class DetailPage extends Command implements Spider
{
    use RandomUserAgent;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zct:detail {id} {city}';
    public $conf;
    const CHINESE_FLAG = '：';

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
        $id = $this->argument('id');
        $city = $this->argument('city');
        $this->info("开始爬取ID是: {$id} 详情页");
        $cookieArr2 = $this->argument('cookies') ?? null;
        if(empty($cookieArr2)){
            $cookieArr2 = file_get_contents(storage_path('cookies.json'));
            $cookieArr2 = json_decode($cookieArr2, true);
        }
        $jar2 = new CookieJar(false, $cookieArr2);

        $html = QueryList::get($this->url($id), [], [
            'cookies' => $jar2
        ])->encoding('UTF-8', 'GB2312')->removeHead()->getHtml();
        $ql = QueryList::html($html);
        // 解析联系人和手机号
        $contacts_str = $ql->find('.fast_show_list')->eq(0)->text();
        $phone_str = $ql->find('.fast_show_list')->eq(1)->text();
        list($pre, $contacts) = explode(static::CHINESE_FLAG, $contacts_str);
        list($pre, $phone) = explode(static::CHINESE_FLAG, $phone_str);
        $data = [
            'zct_id' => (int)$id,
            'city' => $city,
            'title' => $ql->find('.fast_show_top_h1')->text(),
            'company' => $ql->find('.fast_show_com_name')->eq(0)->text(),
            'update_time' => Str::substr($ql->find('.fast_show_top_date')->text(), -10),
            'end_time' => Str::substr($ql->find('.fast_show_top_xz')->text(), -10),
            'contacts' => trim($contacts),
            'phone' => trim($phone),
            'requirement' => $ql->find('.fast_show_com_name_p')->text(),
        ];
        dd($data);
    }


    public function url(int $id)
    {
        return Arr::get($this->conf, 'domain') . "/once/c_show-id_{$id}.html";
    }

    public function params(): array
    {
        // TODO: Implement params() method.
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
