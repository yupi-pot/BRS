<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends Model
{
    use HasFactory;

    /**
     * Название таблицы в БД
     */
    protected $table = 'notes';

    /**
     * Поля, которые можно массово заполнять
     * (защита от mass assignment атак)
     */
    protected $fillable = [
        'title',
        'content',
    ];

    /**
     * Поля, которые должны быть преобразованы в типы
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
