# 📝 Notes Service - Мини-сервис заметок

RESTful API для управления заметками с React UI.

**Стек:** Laravel 12, PHP 8.4, MySQL 8.0, React 18, Docker, Nginx

## Требования

- Docker Desktop
- Свободные порты: 8080, 3307

## Инструкция по развертыванию

### Запуск backend (одна команда)

```bash
docker compose up -d
```

API доступен на http://localhost:8080/api

**Время первого запуска:** 2-3 минуты (скачивание образов)

### Запуск frontend (опционально)

```bash
cd frontend
npm install
npm run dev
```

UI доступен на http://localhost:3000

## API Endpoints

**Base URL:** `http://localhost:8080/api`

```http
GET    /api/notes       # Получить все заметки
GET    /api/notes/{id}  # Получить заметку
POST   /api/notes       # Создать заметку
PUT    /api/notes/{id}  # Обновить заметку
DELETE /api/notes/{id}  # Удалить заметку
```

## Документация

**Swagger UI:** http://localhost:8080/api-documentation.html

## Тестирование

```bash
docker exec notes_app php artisan test
```

Результат: 12 тестов, 49 assertions

## Полезные команды

```bash
# Остановить
docker compose down

# Перезапустить
docker compose restart

# Логи
docker compose logs -f

# Статус
docker compose ps
```

## База данных

- **Host:** localhost:3307
- **Database:** notes_db
- **User:** notes_user
- **Password:** notes_pass

## Структура проекта

```
tz_BRS/
├── docker-compose.yml    # Конфигурация Docker
├── backend/              # Laravel API
├── frontend/             # React UI
└── nginx/                # Web сервер
```
