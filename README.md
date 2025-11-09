# Task Management API

Laravel-приложение для управления задачами с токен-аутентификацией, RabbitMQ и многослойной архитектурой.

## Технологический стек

- PHP 8.2
- Laravel 11
- PostgreSQL 15.0
- RabbitMQ 3
- Docker & Docker Compose
- Spatie Media Library

## Установка

### Клонирование репозитория

```bash
git clone https://github.com/Weberd/tasks-api
cd task-management-api
```

### Копирование файла окружения

```bash
cp .env.example .env
```

### Запуск Docker контейнеров

```bash
docker-compose up -d
```

### Установка зависимостей

```bash
docker-compose exec app composer install
```

### Генерация ключа приложения

```bash
docker-compose exec app php artisan key:generate
```

### Публикация конфигурации Media Library и миграция

```bash
docker-compose exec app php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
```

## Применение миграций

```bash
docker-compose exec app php artisan migrate --seed
```

### Создание символической ссылки для хранилища

```bash
docker-compose exec app php artisan storage:link
```

### Запуск worker для обработки очереди

```bash
docker-compose exec app php artisan queue:work redis --queue=notifications
```

## Проверка работоспособности

Запуск тестов

```bash
php artisan test
```

## API Endpoints

### Аутентификация

#### Регистрация
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Вход
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Выход
```http
POST /api/logout
Authorization: Bearer {api_token}
```

### Задачи

Все запросы требуют заголовок: `Authorization: Bearer {api_token}`

#### Получение списка задач проекта
```http
GET /api/projects/{project_id}/tasks?status=planned&assignee_id=1&completion_date=2024-12-31
```

**Параметры фильтрации:**
- `status` - статус задачи (planned, in_progress, done)
- `assignee_id` - ID исполнителя
- `completion_date` - дата завершения (YYYY-MM-DD)

#### Создание задачи
```http
POST /api/projects/{project_id}/tasks
Content-Type: multipart/form-data

{
  "title": "Implement API",
  "description": "Create REST API endpoints",
  "status": "planned",
  "completion_date": "2024-12-31",
  "assignee_id": 1,
  "attachment": <file>
}
```

#### Получение задачи
```http
GET /api/tasks/{id}
```

#### Обновление задачи
```http
PUT /api/tasks/{id}
Content-Type: multipart/form-data

{
  "title": "Updated title",
  "status": "in_progress",
  "attachment": <file>
}
```

#### Удаление задачи
```http
DELETE /api/tasks/{id}
```

## Ответы API

### Успешный ответ
```json
{
  "success": true,
  "message": "Task created successfully",
  "data": {
    "id": 1,
    "title": "Task title",
    "description": "Task description",
    "status": "planned",
    "completion_date": "2024-12-31",
    "project": {
      "id": 1,
      "name": "Project Name"
    },
    "assignee": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "attachments": [
      {
        "id": 1,
        "name": "document",
        "file_name": "document.pdf",
        "mime_type": "application/pdf",
        "size": 1024,
        "url": "http://localhost:8080/storage/1/document.pdf"
      }
    ]
  }
}
```

### Ошибка валидации
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "title": ["The title field is required."],
    "status": ["Status must be one of: planned, in_progress, done"]
  }
}
```

### Ошибка
```json
{
  "success": false,
  "message": "Task not found"
}
```

## Особенности реализации

### Транзакционность
Запрос `GET /api/projects/{project_id}/tasks` выполняется в транзакции с блокировкой `lockForUpdate()` для предотвращения ошибок параллельного выполнения.

### RabbitMQ
При создании задачи автоматически отправляется уведомление на email исполнителя через очередь RabbitMQ.

### Медиа-файлы
Загрузка файлов реализована через Spatie Media Library. Файлы хранятся локально в `storage/app/public`. В ответе API возвращается URL файла.

### Валидация
Все входные данные валидируются через Form Request классы с понятными сообщениями об ошибках.

## Доступные сервисы

- **API**: http://localhost:8080
- **RabbitMQ Management**: http://localhost:15672 (guest/guest)
- **MySQL**: localhost:3307

## Структура проекта

```
app/
├── Entities/           # Eloquent модели
├── Repositories/       # Репозитории для доступа к данным
│   └── Contracts/      # Интерфейсы репозиториев
├── Services/           # Бизнес-логика
├── Http/
│   ├── Controllers/    # Контроллеры
│   ├── Requests/       # Form Request классы
│   └── Resources/      # API Resources
├── Jobs/               # Задачи для очереди
├── Notifications/      # Уведомления
└── Providers/          # Service Providers
```

## Тестирование

Пример cURL запроса:

```bash
# Регистрация
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"password123","password_confirmation":"password123"}'

# Создание задачи
curl -X POST http://localhost:8080/api/projects/1/tasks \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -F "title=New Task" \
  -F "description=Task description" \
  -F "status=planned" \
  -F "assignee_id=1" \
  -F "attachment=@/path/to/file.pdf"
```

## Остановка приложения

```bash
docker-compose down
```

## Очистка данных

```bash
docker-compose down -v
```
