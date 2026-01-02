# Laravel 12 + Docker (PHP-FPM 8.4, Nginx, PostgreSQL 18)

## 0. Совместимость

Окружение тестировалось **только на Ubuntu 24.04.3 LTS**.
Корректная работа на других ОС (Windows, macOS, другие дистрибутивы Linux) не гарантируется и может потребовать ручной доработки.

## 1. Подготовка `.env`

1. В корне репозитория создайте файл окружения на основе шаблона:
    ```bash
   cp .env.example .env
    ```
2. При необходимости измените базовые параметры (APP_NAME, APP_URL и т.п.).
3. Убедитесь, что в `.env` заданы переменные для базы данных, которые используются Makefile:

Пример:
```dotenv
DB_NAME=laravel_db
DB_USER=laravel_user
DB_PASSWORD=laravel_user_password
DB_HOST=postgres
DB_PORT=5432
```


Эти значения:
- используются при запуске PostgreSQL,
- подставляются в `.env` Laravel в процессе `make install`.

## 2. Стек и структура

Стек:
- PHP 8.4 (FPM) — контейнер `laravel_php`
- Nginx — контейнер `laravel_nginx`
- PostgreSQL 18 — контейнер `laravel_postgres`
- Laravel 12 — код в директории `src/`
- Makefile — набор утилитных команд

Основные файлы:
- compose.yaml — конфигурация Docker Compose
- docker/php/Dockerfile — образ PHP + необходимые расширения + Composer
- docker/nginx/default.conf — конфигурация Nginx
- Makefile — команды для управления окружением
- src/ — директория приложения Laravel (создаётся при установке)

## 3. Требования

На хосте должны быть установлены:
- Docker
- Docker Compose
- make
- git (для клонирования репозитория)

## 4. Установка проекта с нуля

> Этот раздел для создания нового проекта. Если вы клонировали существующий репозиторий — см. раздел 4.1.

Из корня проекта выполните:

```bash
  make install
```

Сценарий выполняет:
1. Останавливает контейнеры и удаляет их
2. Пересоздаёт директорию src/ с корректными правами.
3. Собирает и запускает контейнеры (php, nginx, postgres).
4. Устанавливает Laravel 12 в src/.
5. Обновляет .env Laravel на основе переменных из корневого .env (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD).
6. Проверяет подключение к базе данных.
7. Запускает миграции (php artisan migrate:fresh --force) и php artisan storage:link.

После успешной установки приложение доступно по адресу:

http://localhost

## 4.1. Клонирование существующего проекта

Если вы клонировали репозиторий с уже существующим кодом в `src/`:

1. Создайте `.env` в корне проекта:
   ```bash
   cp .env.example .env
   ```

2. Запустите контейнеры:
   ```bash
   make up
   ```

3. Войдите в контейнер и установите зависимости:
   ```bash
   make shell
   ```

4. Внутри контейнера:
   ```bash
   # Установка зависимостей
   composer install

   # Копирование .env (если нет)
   cp .env.example .env

   # Генерация ключа приложения
   php artisan key:generate

   # Генерация JWT секрета (обязательно!)
   php artisan jwt:secret

   # Миграции
   php artisan migrate

   # Символическая ссылка на storage
   php artisan storage:link
   ```

5. Приложение доступно по адресу: http://localhost

### Запуск тестов

```bash
make shell
composer test
```

> JWT_SECRET для тестов уже настроен в `phpunit.xml`, поэтому тесты работают без дополнительной настройки.

## 5. Основные команды Makefile

Запуск контейнеров:
```bash
  make up
```

Остановка и удаление контейнеров:
```bash
  make down
```

Пересборка образов:
```bash
  make build
```

Перезапуск всех контейнеров:
```bash
  make restart
```

Логи всех сервисов:
```bash
  make logs
```

Логи отдельных сервисов:
```bash
  make logs-nginx
```

```bash
  make logs-php
```

```bash
  make logs-db
```

Доступ в контейнер PHP:
```bash
  make shell
```

Доступ к PostgreSQL внутри контейнера:
```bash
  make shell-db
```

Команды Composer внутри контейнера:
```bash
  make composer about
```

```bash
  make composer install
```

```bash
  make composer dump-autoload
```

Примеры команд Artisan внутри контейнера:

```bash
  make artisan route:list
```

```bash
  make artisan migrate
```

```bash
  make artisan cache:clear
```

Шорткаты для миграций:

```bash
  make migrate
```

```bash
  make fresh
```

## 6. Полная очистка

Полный сброс окружения и кода приложения:

```bash
  make clean
```

Команда:
- останавливает контейнеры,
- удаляет volumes данного проекта,
- удаляет директорию src/.

> Используйте только если готовы удалить код и данные проекта!