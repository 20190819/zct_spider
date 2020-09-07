<?php
/**
 * Created by phpstorm.
 * User: yangliang
 * Date: 2020/9/4 0004
 * Time: 10:28
 */


namespace App\Console\Commands;


interface Spider
{
    public function params(): array;

    public function rules(): array;

    public function range(): string;
}
