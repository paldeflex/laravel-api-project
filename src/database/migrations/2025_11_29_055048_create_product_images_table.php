<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->comment('Изображение товара');

            $table->id();
            $table->foreignId('product_id')->comment('Товар, для которого изображение')->constrained()->cascadeOnDelete();
            $table->string('path')->comment('Путь до файла');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
