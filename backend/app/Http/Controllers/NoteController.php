<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class NoteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/notes",
     *     tags={"Notes"},
     *     summary="Получить список всех заметок",
     *     description="Возвращает все заметки, отсортированные по дате создания",
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Моя заметка"),
     *                     @OA\Property(property="content", type="string", example="Содержимое заметки"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Notes retrieved successfully")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        // Получаем все заметки из БД, сортируем по дате создания (новые первые)
        $notes = Note::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $notes,
            'message' => 'Notes retrieved successfully'
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/notes/{id}",
     *     tags={"Notes"},
     *     summary="Получить заметку по ID",
     *     description="Возвращает одну заметку по её идентификатору",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID заметки",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Заметка найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Моя заметка"),
     *                 @OA\Property(property="content", type="string", example="Содержимое"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="Note retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заметка не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Note not found")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        // Ищем заметку по ID
        $note = Note::find($id);

        // Если не найдена - возвращаем ошибку 404
        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $note,
            'message' => 'Note retrieved successfully'
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/notes",
     *     tags={"Notes"},
     *     summary="Создать новую заметку",
     *     description="Создает новую заметку с валидацией данных",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","content"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Новая заметка"),
     *             @OA\Property(property="content", type="string", example="Текст заметки")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Заметка успешно создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="Note created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        // ВАЛИДАЦИЯ: проверяем что данные корректны
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',  // Обязательно, строка, макс 255 символов
                'content' => 'required|string',         // Обязательно, строка
            ]);
        } catch (ValidationException $e) {
            // Если валидация не прошла - возвращаем ошибки
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Создаем новую заметку в БД
        $note = Note::create($validated);

        return response()->json([
            'success' => true,
            'data' => $note,
            'message' => 'Note created successfully'
        ], 201);  // 201 = Created
    }

    /**
     * @OA\Put(
     *     path="/api/notes/{id}",
     *     tags={"Notes"},
     *     summary="Обновить заметку",
     *     description="Обновляет существующую заметку. Можно обновить отдельные поля.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID заметки",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=255, example="Обновленный заголовок"),
     *             @OA\Property(property="content", type="string", example="Обновленное содержимое")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Заметка обновлена",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Note updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заметка не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Note not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Ищем заметку
        $note = Note::find($id);

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found'
            ], 404);
        }

        // ВАЛИДАЦИЯ: title и content опциональны при обновлении
        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',  // sometimes = если передано, то валидируем
                'content' => 'sometimes|required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Обновляем заметку
        $note->update($validated);

        return response()->json([
            'success' => true,
            'data' => $note,
            'message' => 'Note updated successfully'
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/notes/{id}",
     *     tags={"Notes"},
     *     summary="Удалить заметку",
     *     description="Удаляет заметку по её идентификатору",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID заметки",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Заметка удалена",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Note deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заметка не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Note not found")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        // Ищем заметку
        $note = Note::find($id);

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found'
            ], 404);
        }

        // Удаляем из БД
        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully'
        ], 200);
    }
}
