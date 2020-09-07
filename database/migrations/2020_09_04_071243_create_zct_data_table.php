<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZctDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zct_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('zct_id')->index()->comment('证才通数据源ID');
            $table->string('city')->nullable()->index()->comment('职位所在城市');
            $table->string('title')->nullable()->comment('标题');
            $table->string('company')->nullable()->comment('公司');
            $table->string('update_time')->nullable()->index()->comment('职位发布(更新)时间');
            $table->string('end_time')->nullable()->index()->comment('截止时间');
            $table->string('contacts')->nullable()->comment('联系人');
            $table->string('phone')->nullable()->comment('手机号');
            $table->text('requirement')->nullable()->comment('职位要求');
            $table->timestamps();

            $table->unique(['title', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zct_data');
    }
}
