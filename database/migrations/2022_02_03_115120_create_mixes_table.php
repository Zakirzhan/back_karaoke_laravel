<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMixesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mixes', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('yt_id', 15)->unique();
            $table->tinyInteger('status')->nullable()->default(0)->comment('0 - nothing, 1 - downloading youtube, 2 - track uploaded, 3 - creating mix, 4 - ready');
            $table->string('track_id', 40)->nullable()->comment('Uploaded Track ID');
            $table->string('task_id', 40)->nullable()->comment('Task ID, by using this ID, we can get Status of task');
            $table->string('mix_id', 40)->nullable()->comment('Created MIX ID, by using this ID');
            $table->tinyInteger('server_id')->default(0)->comment('0 - first server, 1 -second server and etc.');
            $table->string('vocals_url')->nullable();
            $table->string('accompaniment_url')->nullable(); 
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->useCurrent(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mixes');
    }
}
