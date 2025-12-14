<?php

use App\Enums\Products\ProductStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->nullOnDelete();
            $table->string('name')->comment('Название');
            $table->text('description')->nullable()->comment('Описание');
            $table->integer('quantity')->nullable()->comment('Количество');
            $table->integer('price')->nullable()->comment('Цена в копейках');
            $table->string('status')->default(ProductStatus::Draft->value)->comment('Статус');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
