<?php

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для API заметок
 *
 * RefreshDatabase - автоматически очищает БД после каждого теста
 */
class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест: получение списка всех заметок
     * GET /api/notes
     */
    public function test_can_get_all_notes(): void
    {
        // Создаем 3 тестовые заметки
        Note::factory()->count(3)->create();

        // Делаем GET запрос
        $response = $this->getJson('/api/notes');

        // Проверяем что запрос успешен (статус 200)
        $response->assertStatus(200);

        // Проверяем структуру ответа
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'title', 'content', 'created_at', 'updated_at']
            ],
            'message'
        ]);

        // Проверяем что вернулось 3 заметки
        $response->assertJsonCount(3, 'data');
    }

    /**
     * Тест: получение одной заметки по ID
     * GET /api/notes/{id}
     */
    public function test_can_get_single_note(): void
    {
        // Создаем заметку
        $note = Note::factory()->create([
            'title' => 'Test Note',
            'content' => 'Test Content'
        ]);

        // Запрашиваем эту заметку
        $response = $this->getJson("/api/notes/{$note->id}");

        // Проверяем успех
        $response->assertStatus(200);

        // Проверяем что вернулась правильная заметка
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $note->id,
                'title' => 'Test Note',
                'content' => 'Test Content'
            ]
        ]);
    }

    /**
     * Тест: ошибка 404 при запросе несуществующей заметки
     */
    public function test_returns_404_for_non_existent_note(): void
    {
        $response = $this->getJson('/api/notes/999');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Note not found'
        ]);
    }

    /**
     * Тест: создание новой заметки
     * POST /api/notes
     */
    public function test_can_create_note(): void
    {
        $noteData = [
            'title' => 'New Note',
            'content' => 'New Content'
        ];

        $response = $this->postJson('/api/notes', $noteData);

        // Проверяем статус 201 (Created)
        $response->assertStatus(201);

        // Проверяем ответ
        $response->assertJson([
            'success' => true,
            'data' => $noteData,
            'message' => 'Note created successfully'
        ]);

        // Проверяем что заметка появилась в БД
        $this->assertDatabaseHas('notes', $noteData);
    }

    /**
     * Тест: валидация при создании заметки (пустые поля)
     */
    public function test_create_note_validation_fails_on_empty_fields(): void
    {
        $response = $this->postJson('/api/notes', [
            'title' => '',
            'content' => ''
        ]);

        // Статус 422 (Validation Error)
        $response->assertStatus(422);

        // Проверяем что есть ошибки валидации
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => ['title', 'content']
        ]);
    }

    /**
     * Тест: валидация title (больше 255 символов)
     */
    public function test_create_note_validation_fails_on_long_title(): void
    {
        $response = $this->postJson('/api/notes', [
            'title' => str_repeat('a', 256), // 256 символов
            'content' => 'Valid content'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    /**
     * Тест: обновление заметки
     * PUT /api/notes/{id}
     */
    public function test_can_update_note(): void
    {
        // Создаем заметку
        $note = Note::factory()->create([
            'title' => 'Old Title',
            'content' => 'Old Content'
        ]);

        // Обновляем её
        $updatedData = [
            'title' => 'Updated Title',
            'content' => 'Updated Content'
        ];

        $response = $this->putJson("/api/notes/{$note->id}", $updatedData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => $updatedData,
            'message' => 'Note updated successfully'
        ]);

        // Проверяем что в БД обновилось
        $this->assertDatabaseHas('notes', $updatedData);
    }

    /**
     * Тест: частичное обновление заметки (только title)
     */
    public function test_can_partially_update_note(): void
    {
        $note = Note::factory()->create([
            'title' => 'Old Title',
            'content' => 'Original Content'
        ]);

        // Обновляем только title
        $response = $this->putJson("/api/notes/{$note->id}", [
            'title' => 'New Title'
        ]);

        $response->assertStatus(200);

        // Проверяем что title обновился, а content остался прежним
        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'New Title',
            'content' => 'Original Content'
        ]);
    }

    /**
     * Тест: удаление заметки
     * DELETE /api/notes/{id}
     */
    public function test_can_delete_note(): void
    {
        $note = Note::factory()->create();

        $response = $this->deleteJson("/api/notes/{$note->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Note deleted successfully'
        ]);

        // Проверяем что заметки больше нет в БД
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    /**
     * Тест: ошибка 404 при удалении несуществующей заметки
     */
    public function test_delete_returns_404_for_non_existent_note(): void
    {
        $response = $this->deleteJson('/api/notes/999');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Note not found'
        ]);
    }
}
