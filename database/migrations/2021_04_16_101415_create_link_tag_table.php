<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('link_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('link_id')->default(0);
            $table->foreign('link_id')
                ->references('id')->on('links')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->unsignedBigInteger('tag_id')->default(0);
            $table->foreign('tag_id')
                ->references('id')->on('tags')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('link_tag');
    }
}
