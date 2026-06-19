# CLAUDE.md

Этот файл даёт указания Claude Code (claude.ai/code) при работе с кодом в этом репозитории.

## Проект

SEO-чекер (Laravel 13 / PHP 8.3 / MySQL по спецификации, SQLite в разработке). По набору URL анализирует базовые SEO-параметры (title, description, h1, иерархия заголовков, внешние ссылки, микроразметка Open Graph / Schema.org, robots.txt, sitemap.xml), сохраняет каждый запуск как историческую «проверку» (audit) и показывает список истории плюс страницы детализации по каждой проверке. Исходная спецификация — в `README.md` (на русском). Строки интерфейса и сообщения об ошибках — на русском.

## Команды

- `composer setup` — первичная настройка: установка зависимостей, копирование `.env`, генерация ключа, миграции, сборка ассетов.
- `composer dev` — запускает всё для локальной разработки одновременно: `php artisan serve`, `queue:listen`, `pail` (живые логи) и `vite`.
- `composer test` — сбрасывает конфиг и запускает `php artisan test`. Тесты идут на in-memory SQLite (см. `phpunit.xml`), независимо от `.env`.
- Один тест: `php artisan test --filter=SomeTest` (или `--filter=SomeTest::method`). Наборы — `Unit` (`tests/Unit`) и `Feature` (`tests/Feature`).
- `vendor/bin/pint` — форматирование PHP (Laravel Pint — линтер/форматтер проекта).
- `npm run dev` / `npm run build` — ассеты Vite + Tailwind v4.

## Архитектура

Конвейер проверки целиком лежит в `app/Services/Seo/` и намеренно разбит на маленькие классы с единственной ответственностью:

- **`PageDownloader::download($url)`** — загружает страницу через фасад `Http` с отслеживанием редиректов, возвращает массив с Symfony `DomCrawler\Crawler` плюс `status_code` / `final_url`. Все остальные HTML-проверки потребляют этот `Crawler`.
- **`Checkers/*Checker`** — по одному классу на правило SEO (`Title`, `Description`, `Heading`, `Links`, `Microdata`). Каждый принимает `Crawler` (некоторые ещё и базовый URL) и возвращает обычный массив. **Соглашение:** HTML-чекеры возвращают `marker` со значением `'green'`/`'red'`, плюс `error` (null, если всё хорошо) и поля под конкретную проверку (`length`, счётчики, `formats`). `HeadingChecker` возвращает и результат `h1`, и результат `structure` (проверяет, что заголовки начинаются с h1 и не пропускают уровни).
- **`RootFilesChecker::checkFile($baseUrl, $fileName)`** — отдельно проверяет по HTTP корневые файлы сайта (`robots.txt`, `sitemap.xml`); тот же формат `marker`/`error`.
- **`ReportStorageService`** — единственное, что обращается к БД. `createAudit` → `saveResultForUrl` (одна транзакция БД на каждый URL, пишет `AuditUrl` + `AuditResult`) → `completeAudit`. Чтение: `getAuditHistory` (с пагинацией, `status = completed`) и `getAuditDetail`.

**Модель данных:** `Audit` (1) → много `AuditUrl` (1) → один `AuditResult`. У `Audit` есть `status` (`processing` → `completed`). `AuditResult` разворачивает каждую проверку в колонки (булевы `*_is_valid`, `*_error_reason`, длины, счётчики ссылок, булевы маркеры); `schema_formats` — каст JSON/array.

**Веб-слой:** `routes/web.php` + `AuditHistoryController` обслуживают `/history` (список) и `/history/{id}` (детализация), рендерятся через `resources/views/history/*.blade.php`.

### Важно: конвейер ещё не связан от начала до конца

**Нет контроллера/сервиса, который прогонял бы чекеры по присланным пользователем URL и передавал результаты в `ReportStorageService`.** Вместо него стоят два «строительных» маршрута: `/test-seo` (прогоняет все чекеры по захардкоженному URL и выдаёт JSON) и `/test-audit` (пишет захардкоженный фейковый результат через `ReportStorageService`). `saveToFavorites` — заглушка (ничего не сохраняет, хотя таблица `saved_reports` существует).

Учти **несовпадение форматов**, которое нужно согласовать при связывании: чекеры выдают `marker`/`error`, а `ReportStorageService::saveResultForUrl` ждёт `$data[...]['valid']` / `['reason']` (и ключи верхнего уровня вроде `http_code`, `external_links_nofollow`, `og_marker`). Новый код, связывающий загрузку → проверку → сохранение, должен переводить один формат в другой (или нормализовать один из них).
