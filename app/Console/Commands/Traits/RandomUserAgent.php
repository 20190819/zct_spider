<?php
/**
 * Created by phpstorm.
 * User: yangliang
 * Date: 2020/9/4 0004
 * Time: 10:24
 */


namespace App\Console\Commands\Traits;


use Illuminate\Support\Arr;

trait RandomUserAgent
{
    public function randomUserAgent(array $conf): string
    {
        $user_agents = Arr::get($conf, 'user_agent');
        return Arr::random($user_agents);
    }
}
