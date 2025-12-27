<?php

use App\Enums\Report\ReportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_logs', function (Blueprint $table): void {
            $table->comment('Логи генерации отчетов');

            $table->id();
            $table->foreignId('user_id')
                ->comment('Пользователь, запросивший отчет')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('report_type')
                ->comment('Тип отчета: products, product_reviews, users');
            $table->string('file_name')
                ->nullable()
                ->comment('Имя сгенерированного файла');
            $table->string('file_path')
                ->nullable()
                ->comment('Путь к файлу в storage');
            $table->string('status')
                ->default(ReportStatus::Pending->value)
                ->comment('Статус: pending, processing, completed, failed');
            $table->text('error_message')
                ->nullable()
                ->comment('Сообщение об ошибке при неудачной генерации');
            $table->unsignedTinyInteger('attempts')
                ->default(0)
                ->comment('Количество попыток генерации');
            $table->timestamp('started_at')
                ->nullable()
                ->comment('Время начала генерации');
            $table->timestamp('completed_at')
                ->nullable()
                ->comment('Время завершения генерации');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_logs');
    }
};
