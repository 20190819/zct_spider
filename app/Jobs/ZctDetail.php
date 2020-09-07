<?php

namespace App\Jobs;

use App\Models\ZctData;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use QL\QueryList;

class ZctDetail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $conf;
    public $id = 0;
    public $city;
    public $cookieArr2;
    const CHINESE_FLAG = '：';


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $id, string $city, array $cookieArr2 = null)
    {
        $this->queue = 'default';
        $this->delay = 5;
        $this->id = $id;
        $this->city = $city;
        $this->conf = config('zct');
        if (empty($cookieArr2)) {
            $cookieArr2 = file_get_contents(storage_path('cookies.json'));
            $cookieArr2 = json_decode($cookieArr2, true);
        }
        $this->cookieArr2 = $cookieArr2;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->id) {
//            echo("开始爬取ID是: {$this->id} 详情页" . PHP_EOL);

            $jar2 = new CookieJar(false, $this->cookieArr2);

            $html = QueryList::get($this->url($this->id), [], [
                'cookies' => $jar2
            ])->encoding('UTF-8', 'GB2312')->removeHead()->getHtml();
            $ql = QueryList::html($html);
            // 解析联系人和手机号
            $contacts_str = $ql->find('.fast_show_list')->eq(0)->text();
            $phone_str = $ql->find('.fast_show_list')->eq(1)->text();
            $contacts_arr = explode(static::CHINESE_FLAG, $contacts_str);
            $phone_arr = explode(static::CHINESE_FLAG, $phone_str);
            $title = $ql->find('.fast_show_top_h1')->text();
            $phone = trim($phone_arr[1] ?? '--');
            $contracts = trim($contacts_arr[1] ?? '--');
            $data = [
                'zct_id' => $this->id,
                'city' => $this->city,
                'company' => $ql->find('.fast_show_com_name')->eq(0)->text(),
                'update_time' => Str::substr($ql->find('.fast_show_top_date')->text(), -10),
                'end_time' => Str::substr($ql->find('.fast_show_top_xz')->text(), -10),
                'contacts' => $contracts,
                'requirement' => $ql->find('.fast_show_com_name_p')->text(),
            ];
            // 入库
            ZctData::query()->firstOrCreate([
                'title' => $title,
                'phone' => $phone
            ], $data);
        }
    }

    public function url(int $id)
    {
        return Arr::get($this->conf, 'domain') . "/once/c_show-id_{$id}.html";
    }
}
