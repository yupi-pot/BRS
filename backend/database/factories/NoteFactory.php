<?php

namespace Database\Factories;

use App\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory для генерации тестовых заметок
 *
 * Используется в тестах для создания фейковых данных
 */
class NoteFactory extends Factory
{
    /**
     * Модель для которой создается Factory
     */
    protected $model = Note::class;

    /**
     * Определяет структуру тестовых данных
     */
    public function definition(): array
    {
        return [
            // faker()->sentence() - генерирует случайное предложение
            'title' => fake()->sentence(3), // 3 слова

            // faker()->paragraph() - генерирует случайный параграф
            'content' => fake()->paragraph(5), // 5 предложений
        ];
    }
}
