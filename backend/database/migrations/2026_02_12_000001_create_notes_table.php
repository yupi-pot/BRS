<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запускается при выполнении миграции
     * (создает таблицу)
     */
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            // ID - уникальный номер заметки (автоинкремент)
            $table->id();

            // Заголовок заметки (обязательное поле, макс 255 символов)
            $table->string('title');

            // Содержимое заметки (длинный текст)
            $table->text('content');

            // Временные метки: created_at и updated_at
            $table->timestamps();
        });
    }

    /**
     * Откат миграции (удаляет таблицу)
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
