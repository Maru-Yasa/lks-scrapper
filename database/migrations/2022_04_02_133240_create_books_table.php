<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string("judul");
            $table->string("img");
            $table->string("penerbit");
            $table->string("penjual");
            $table->string("tanggal_terbit");
            $table->integer("jumlah_halaman");
            $table->string("kompatibilitas");
            $table->string("bahasa");
            $table->string("genre");
            $table->integer("rating");
            $table->integer("jumlah_pemberi_rating");
            $table->integer("harga");
            $table->text("deskripsi");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
};
