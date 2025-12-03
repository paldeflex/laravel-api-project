Небольшой REST API-сервис на Laravel 12 для работы с товарами и отзывами.  
Поддерживает регистрацию / авторизацию по JWT, роли (админ / обычный пользователь), CRUD по товарам и отзывам и единый JSON-формат ошибок.

## Стек

- PHP 8.4
- Laravel 12
- PostgreSQL (через Docker Compose)
- JWT (php-open-source-saver/jwt-auth)
- PHPUnit (feature + unit тесты)

---

## Запуск проекта

### 1. Зависимости

```bash
composer install

### 2. Конфиг окружения

Скопировать `.env`:

```bash
cp .env.example .env
```

Потом в `.env` настроить:

* параметры подключения к БД (`DB_*`)
* JWT-секрет:

  ```bash
  php artisan jwt:secret
  ```

### 3. Docker

Поднять контейнеры:

```bash
docker compose up -d
```

Выполнить миграции (и, при необходимости, сиды):

```bash
docker compose exec php php artisan migrate
# docker compose exec php php artisan db:seed
```

### 4. Запуск сервера

Локально через artisan:

```bash
docker compose exec php php artisan serve --host=0.0.0.0 --port=8000
```

API будет доступен по `http://localhost:8000/api`.

---

## Аутентификация

Используется JWT-аутентификация (guard `auth:api`).

### Регистрация

`POST /api/register`

Тело запроса:

```json
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

Ответ `201 Created`:

```json
{
  "access_token": "…",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### Логин

`POST /api/login`

```json
{
  "email": "user@example.com",
  "password": "secret123"
}
```

* Успех → `200 OK` + payload токена (как при регистрации)
* Ошибка → `401`:

```json
{ "message": "Неверные логин или пароль" }
```

### Текущий пользователь

`GET /api/me` (требуется `Authorization: Bearer <token>`)

Возвращает данные текущего пользователя.

### Логаут

`POST /api/logout` (требуется токен)

```json
{ "message": "Вы вышли из системы" }
```

---

## Товары

### Публичные эндпоинты

#### Список опубликованных товаров

`GET /api/products`

Возвращает пагинированный список (через `ProductListResource`):

* `id`
* `name`
* `description`
* `quantity`
* `price`
* `status`
* `rating` (средний рейтинг отзывов, округлён до 1 знака)
* `images` (URL-ы картинок)

#### Просмотр товара

`GET /api/products/{product}`

Работает только для опубликованных товаров (`ProductStatus::Published` + middleware `product.published`).

Возвращает `ProductDetailResource`:

* поля товара
* `rating`
* `images`
* `reviews` (коллекция `ProductReviewResource`)

Для черновика (`draft`) вернётся:

```json
{
  "message": "Товар не найден"
}
```

с кодом `404`.

### Админ-эндпоинты

Доступны только с JWT токеном и флагом `is_admin = true` (middleware `admin`).

#### Создать товар

`POST /api/products`

Тело (валидация через `StoreProductRequest`):

```json
{
  "name": "Название",
  "description": "Описание",
  "quantity": 10,
  "price": 1000,
  "status": "published",
  "images": [<files>]
}
```

В контроллере данные собираются в DTO `ProductCreateData`, далее работает `ProductService::createProduct()`.

#### Обновить товар

`PATCH /api/products/{product}`

Частичное обновление (валидация через `UpdateProductRequest`).

#### Удалить товар

`DELETE /api/products/{product}`

Мягкое удаление (`SoftDeletes`), код ответа `204 No Content`.

---

## Отзывы

Для всех эндпоинтов отзывов требуется авторизация (JWT).

### Создать отзыв

`POST /api/products/{product}/reviews`

* Работает только для опубликованного товара (`product.published`).
* Используется `StoreProductReviewRequest` + `ProductReviewCreateData`.

Тело:

```json
{
  "text": "Комментарий",
  "rating": 5
}
```

Ответ — `ProductReviewResource`.

### Обновить отзыв

`PATCH /api/products/{product}/reviews/{review}`

* Авторизация: через `ProductReviewPolicy@update`
* Данные собираются в DTO `ProductReviewUpdateData`

Можно обновить `text` и/или `rating`.

### Удалить отзыв

`DELETE /api/products/{product}/reviews/{review}`

* Авторизация: `ProductReviewPolicy@delete`
* Доп. проверка: middleware `review.belongs-to-product` (отзыв должен относиться к указанному товару).

При попытке удалить отзыв другого товара:

```json
{
  "message": "Доступ запрещён"
}
```

с кодом `403`.

---

## Обработка ошибок

В `bootstrap/app.php` настроена централизованная обработка исключений:

* Для `api/*` (и JSON-запросов) **все ошибки возвращаются в JSON-формате**.
* Основные случаи:

    * Не найден объект (route model binding / `ModelNotFoundException`):

      ```json
      { "message": "Объект не найден" }
      ```

      `404 Not Found`
    * Неавторизован (JWT отсутствует / просрочен / невалиден):

      ```json
      { "message": "Необходимо авторизоваться" }
      ```

      `401 Unauthorized`
    * Нет прав (policy / middleware `admin` и т.п.):

      ```json
      { "message": "Доступ запрещён" }
      ```

      `403 Forbidden`
    * Ошибки валидации (`ValidationException`):

      ```json
      {
        "message": "Данные некорректны",
        "errors": {
          "field": ["Сообщение об ошибке"]
        }
      }
      ```

      `422 Unprocessable Entity`
    * Любое непойманное исключение:

      ```json
      { "message": "Внутренняя ошибка сервера" }
      ```

      `500 Internal Server Error`

---

## Тесты

Тесты лежат в `tests/Feature` и `tests/Unit`.

* **Feature-тесты**:

    * `AuthControllerTest` — регистрация, логин, me, logout.
    * `ProductControllerTest` — публичный список/просмотр, права админа, рейтинг, доступ к черновикам.
    * `ProductReviewControllerTest` — создание/обновление/удаление отзывов, политика и мидлвары.

* **Unit-тесты**:

    * `ProductServiceTest` — выборка опубликованных товаров, работа с картинками, soft delete.
    * `ProductReviewServiceTest` — создание/обновление/удаление отзывов через сервис.
    * При необходимости можно добавить отдельные тесты на политику и middleware.

Запуск тестов:

```bash
docker compose exec php php artisan test
# или
docker compose exec php ./vendor/bin/phpunit
```

---
