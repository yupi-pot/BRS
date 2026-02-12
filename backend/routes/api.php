<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Здесь регистрируются все API маршруты для приложения.
| Все маршруты автоматически получают префикс /api
|
*/

// Resource маршруты для заметок (создает все 5 методов автоматически)
// GET    /api/notes       -> index()   - все заметки
// GET    /api/notes/{id}  -> show()    - одна заметка
// POST   /api/notes       -> store()   - создать
// PUT    /api/notes/{id}  -> update()  - обновить
// DELETE /api/notes/{id}  -> destroy() - удалить
Route::apiResource('notes', NoteController::class);

// Альтернативный вариант (если нужно контролировать каждый маршрут отдельно):
// Route::get('/notes', [NoteController::class, 'index']);
// Route::get('/notes/{id}', [NoteController::class, 'show']);
// Route::post('/notes', [NoteController::class, 'store']);
// Route::put('/notes/{id}', [NoteController::class, 'update']);
// Route::delete('/notes/{id}', [NoteController::class, 'destroy']);
