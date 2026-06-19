# Документация проекта SEO_checker

Подробное описание: что где лежит и зачем. Документ для человека (в отличие от `CLAUDE.md`, который написан как инструкция для ИИ-ассистента).

---

## 1. Что это за проект

Приложение на **Laravel 13 / PHP 8.3** для SEO-аудита веб-страниц.

Идея: пользователь даёт список URL → приложение скачивает каждую страницу → проверяет базовые SEO-параметры → сохраняет результат в БД как «проверку» (audit) → показывает историю проверок и детальный отчёт по каждой.

> ⚠️ **Текущий статус:** проект собран наполовину. Все «кубики» (загрузчик, чекеры, хранилище, история, шаблоны) написаны, но **не соединены в единый поток**. Пользователь пока не может ввести свои URL и получить отчёт. Подробнее — в разделе 8.

### Что именно проверяется

| Параметр | Где проверяется | Что считается ошибкой |
|---|---|---|
| Тег `<title>` | `TitleChecker` | отсутствует или встречается более одного раза |
| Мета `description` | `DescriptionChecker` | отсутствует или дублируется |
| Заголовок `<h1>` | `HeadingChecker` | отсутствует или больше одного |
| Иерархия заголовков h1–h6 | `HeadingChecker` | не начинается с h1 или пропущен уровень (например, после h2 сразу h4) |
| Внешние ссылки | `LinksChecker` | (не ошибка, а подсчёт: всего / nofollow / dofollow) |
| Open Graph | `MicrodataChecker` | нет тегов `<meta property="og:...">` |
| Schema.org | `MicrodataChecker` | нет JSON-LD / Microdata / RDFa |
| `robots.txt` | `RootFilesChecker` | файл недоступен по HTTP |
| `sitemap.xml` | `RootFilesChecker` | файл недоступен по HTTP |

---

## 2. Стек и зависимости

- **Backend:** PHP 8.3, Laravel 13
- **БД:** MySQL (по спецификации), SQLite (в разработке и тестах)
- **HTML-парсинг:** Symfony `DomCrawler` (CSS-селекторы по DOM)
- **HTTP-запросы:** фасад Laravel `Http` (обёртка над Guzzle)
- **Frontend:** Blade-шаблоны + Tailwind CSS v4, сборка через Vite
- **Тесты:** PHPUnit (in-memory SQLite)
- **Форматирование:** Laravel Pint

---

## 3. Карта проекта — где что лежит

```
SEO_checker/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuditHistoryController.php   ← контроллер истории и детализации
│   │   └── Controller.php              ← базовый контроллер Laravel
│   ├── Models/
│   │   ├── Audit.php                   ← одна проверка (запуск аудита)
│   │   ├── AuditUrl.php                ← один URL внутри проверки
│   │   ├── AuditResult.php             ← результаты SEO по одному URL
│   │   └── User.php                    ← пользователь (стандартный, пока почти не используется)
│   ├── Providers/AppServiceProvider.php
│   └── Services/Seo/                   ← ★ ВСЯ бизнес-логика SEO здесь
│       ├── PageDownloader.php          ← скачивает страницу, отдаёт Crawler
│       ├── RootFilesChecker.php        ← проверяет robots.txt / sitemap.xml
│       ├── ReportStorageService.php    ← единственный, кто пишет/читает БД
│       └── Checkers/                   ← по одному классу на SEO-правило
│           ├── TitleChecker.php
│           ├── DescriptionChecker.php
│           ├── HeadingChecker.php
│           ├── LinksChecker.php
│           └── MicrodataChecker.php
├── database/
│   ├── migrations/                     ← схема БД
│   │   ├── 2026_06_17_*_create_audits_table.php
│   │   ├── 2026_06_17_*_create_audit_urls_table.php
│   │   ├── 2026_06_17_*_create_audit_results_table.php
│   │   └── 2026_06_17_*_create_saved_reports_table.php
│   ├── factories/UserFactory.php
│   └── seeders/DatabaseSeeder.php
├── resources/
│   ├── views/
│   │   ├── welcome.blade.php           ← стартовая страница (стандартная)
│   │   └── history/
│   │       ├── index.blade.php         ← список истории проверок
│   │       └── show.blade.php          ← детальный отчёт по одной проверке
│   ├── css/app.css                     ← Tailwind
│   └── js/app.js
├── routes/
│   ├── web.php                         ← ★ все веб-маршруты + тестовые роуты
│   └── console.php
├── tests/
│   ├── Feature/ExampleTest.php         ← пока заглушки
│   └── Unit/ExampleTest.php
├── config/                             ← стандартные конфиги Laravel
├── README.md                          ← исходное ТЗ (на русском)
├── CLAUDE.md                          ← инструкция для ИИ-ассистента
└── DOCS.md                            ← этот файл
```

---

## 4. Как работает конвейер проверки (по шагам)

Поток данных задуман так (стрелка — «передаёт результат»):

```
URL → PageDownloader → Crawler → [Checkers] → массив результатов → ReportStorageService → БД → Blade-шаблоны
```

### Шаг 1. Загрузка страницы — `PageDownloader::download($url)`

`app/Services/Seo/PageDownloader.php`

- Делает HTTP GET с таймаутом 10 сек и отслеживанием редиректов.
- Возвращает массив:
  ```php
  [
    'success'     => true/false,
    'status_code' => 200,                  // HTTP-код
    'final_url'   => 'https://...',        // конечный URL после редиректов
    'html'        => '<html>...',          // сырой HTML
    'crawler'     => Crawler,              // объект для парсинга (или null при ошибке)
    'error'       => null,                 // текст ошибки или null
  ]
  ```
- При ошибке подключения `success = false`, `crawler = null`, в `error` — текст.

### Шаг 2. Проверки HTML — классы в `Checkers/`

Каждый чекер принимает `Crawler` (а `LinksChecker` ещё и базовый URL) и возвращает массив.

**Общее соглашение для HTML-чекеров:**
- `marker` — `'green'` (всё ок) или `'red'` (есть проблема);
- `error` — текст проблемы или `null`;
- плюс специфичные поля (`length`, счётчики, `formats`).

Кратко по каждому:

- **`TitleChecker`** — ищет `<title>`. Ошибка, если нет или больше одного. Возвращает `marker`, `length` (длина текста), `error`.
- **`DescriptionChecker`** — ищет `<meta name="description">`. Логика аналогична title. Возвращает `marker`, `length`, `error`.
- **`HeadingChecker`** — возвращает **два** результата:
  - `h1` — проверка количества `<h1>`;
  - `structure` — проверка иерархии: первый заголовок должен быть h1, уровни не должны прыгать через один (h2 → h4 = ошибка).
- **`LinksChecker`** — считает внешние ссылки (хост отличается от хоста страницы). Возвращает `external_links_count`, `nofollow_count`, `dofollow_count`. Это подсчёт, не «зелёный/красный».
- **`MicrodataChecker`** — возвращает два блока:
  - `open_graph` — есть ли теги `og:*`;
  - `schema_org` — какие форматы микроразметки найдены (`JSON-LD`, `Microdata`, `RDFa`), список в поле `formats`.

### Шаг 3. Проверка корневых файлов — `RootFilesChecker::checkFile($baseUrl, $fileName)`

`app/Services/Seo/RootFilesChecker.php`

- Собирает URL вида `scheme://host/robots.txt` и делает HTTP-запрос (таймаут 5 сек).
- Возвращает `marker`/`error`. Используется отдельно для `robots.txt` и `sitemap.xml`.

### Шаг 4. Сохранение в БД — `ReportStorageService`

`app/Services/Seo/ReportStorageService.php` — **единственный класс, который трогает базу.**

- `createAudit($userId = null)` — создаёт запись `Audit` со статусом `processing`.
- `saveResultForUrl($audit, $url, $data)` — в одной транзакции пишет `AuditUrl` + `AuditResult` для одного URL.
- `completeAudit($audit)` — переводит статус в `completed`.
- `getAuditHistory($perPage = 15)` — для списка истории: только завершённые проверки, с пагинацией, отсортированные по дате.
- `getAuditDetail($auditId)` — одна проверка со всеми URL и результатами (для страницы детализации).

### Шаг 5. Отображение — контроллер и шаблоны

- `AuditHistoryController::index` → `getAuditHistory` → `history/index.blade.php` (список).
- `AuditHistoryController::show($id)` → `getAuditDetail` → `history/show.blade.php` (детали).

---

## 5. Модель данных (база)

Связи: **`Audit` (1) → много `AuditUrl` (1) → один `AuditResult`.**

```
audits                audit_urls                 audit_results
-------               ----------                 -------------
id                    id                         id
user_id (nullable)    audit_id  ──┐              audit_url_id ──┐
status                url         │ FK           h1_is_valid     │ FK
created_at            http_code   │              h1_error_reason │
updated_at            redirect_final_url         title_is_valid  │
                      created_at                 title_error_reason
                      updated_at                 title_length
                                                 description_is_valid
                                                 description_error_reason
                                                 description_length
                                                 headings_valid
                                                 external_links_count
                                                 external_links_nofollow
                                                 external_links_dofollow
                                                 og_marker
                                                 schema_marker
                                                 schema_formats (JSON)
                                                 robots_marker
                                                 sitemap_marker
```

Пояснения:
- **`audits`** — один запуск аудита. `status`: `processing` (идёт) → `completed` (готово). `user_id` пока необязателен.
- **`audit_urls`** — конкретный URL внутри запуска. Хранит HTTP-код и конечный URL после редиректов.
- **`audit_results`** — «плоская» таблица: каждая проверка разложена в отдельные колонки. Булевы `*_is_valid` / `*_marker` — пройдена ли проверка, `*_error_reason` — текст ошибки, `*_length` / счётчики — числовые данные. `schema_formats` — JSON-массив форматов микроразметки (каст `array` в модели).
- **`saved_reports`** — таблица для «избранных» отчётов (связь audit ↔ user). Существует, но **функционал не реализован** — `saveToFavorites` пока заглушка.

Eloquent-связи в моделях:
- `Audit::urls()` → hasMany `AuditUrl`
- `AuditUrl::audit()` → belongsTo `Audit`; `AuditUrl::result()` → hasOne `AuditResult`
- `AuditResult::auditUrl()` → belongsTo `AuditUrl`

---

## 6. Маршруты (`routes/web.php`)

| Метод | URL | Назначение | Статус |
|---|---|---|---|
| GET | `/` | стартовая страница (стандартный welcome) | работает |
| GET | `/history` | список истории проверок | работает |
| GET | `/history/{id}` | детальный отчёт по проверке | работает |
| POST | `/history/{id}/save` | «сохранить в избранное» | **заглушка** |
| GET | `/test-seo` | прогоняет все чекеры по захардкоженному `https://laravel.com` и выдаёт JSON | временный/тестовый |
| GET | `/test-audit` | пишет захардкоженный фейковый результат в БД через `ReportStorageService` | временный/тестовый |

`/test-seo` и `/test-audit` — это «леса» для проверки кусков логики вручную. В финальном продукте их не должно быть.

---

## 7. Как запустить и работать локально

```bash
composer setup     # первичная настройка (deps, .env, ключ, миграции, ассеты)
composer dev       # запуск всего сразу: сервер + очередь + логи + vite
```

Полезное:
- `composer test` — тесты (in-memory SQLite, не трогает реальную БД).
- `php artisan test --filter=ИмяТеста` — один тест.
- `vendor/bin/pint` — форматирование кода.
- `npm run dev` / `npm run build` — фронтенд-ассеты отдельно.

Проверить руками без UI:
- открыть `/test-seo` — увидеть JSON-результат всех чекеров;
- открыть `/test-audit`, затем `/history` — увидеть, как фейковый отчёт попадает в историю.

---

## 8. Что НЕ доделано (важно)

1. **Нет «склейки» конвейера.** Отсутствует контроллер/сервис, который:
   - принимает список URL от пользователя (формы ввода тоже нет);
   - для каждого URL вызывает `PageDownloader` → чекеры → собирает результат;
   - передаёт результат в `ReportStorageService` и завершает аудит.

   Сейчас эту роль «играют» только тестовые роуты `/test-seo` и `/test-audit`.

2. **Несовпадение форматов данных** — главная техническая загвоздка при склейке:
   - чекеры отдают `marker` (`'green'`/`'red'`) и `error`;
   - а `ReportStorageService::saveResultForUrl` ожидает другой формат: `$data['title']['valid']`, `$data['title']['reason']`, и плоские ключи верхнего уровня (`http_code`, `external_links_nofollow`, `og_marker`, `schema_marker` и т.д.).

   То есть нужен слой-адаптер, который переведёт вывод чекеров в формат, который ждёт хранилище (см. пример ожидаемого формата в роуте `/test-audit`). Альтернатива — привести один из форматов к другому.

3. **`saveToFavorites` — заглушка.** Возвращает сообщение об успехе, но ничего не пишет, хотя таблица `saved_reports` готова.

4. **Тесты — пустые заглушки** (`ExampleTest`). Реальных тестов на чекеры/хранилище пока нет.

### Логичные следующие шаги
1. Написать класс-адаптер (нормализатор) между выводом чекеров и форматом `saveResultForUrl`.
2. Создать оркестратор аудита (например, `AuditRunner` или метод контроллера): URL[] → загрузка → проверки → адаптер → хранилище.
3. Сделать форму ввода URL и контроллер запуска проверки.
4. Реализовать `saveToFavorites` с реальной записью в `saved_reports`.
5. Удалить тестовые роуты `/test-seo`, `/test-audit`.
6. Покрыть чекеры юнит-тестами.
