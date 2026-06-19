<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SEO-проверка')</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; max-width: 960px; margin: 30px auto; padding: 0 20px; color: #222; }
        h1 { margin-bottom: 8px; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .card { border: 1px solid #ddd; padding: 15px 18px; margin: 14px 0; border-radius: 8px; }
        .green { color: #1a7f37; font-weight: bold; }
        .red { color: #d1242f; font-weight: bold; }
        .muted { color: #888; font-size: 14px; }
        .info { display: flex; flex-wrap: wrap; gap: 8px; padding: 6px 0; border-bottom: 1px solid #eee; }
        .label { width: 190px; font-weight: bold; color: #555; }
        .empty { color: #999; text-align: center; padding: 40px; }
        .btn {
            display: inline-block; padding: 9px 16px; border-radius: 6px; border: none;
            background: #0066cc; color: #fff; font-size: 15px; cursor: pointer; text-decoration: none;
        }
        .btn:hover { background: #0052a3; text-decoration: none; }
        .btn-secondary { background: #4b5563; }
        .btn-secondary:hover { background: #374151; }
        .btn-row { display: flex; gap: 10px; margin: 18px 0; flex-wrap: wrap; }
        .flash { padding: 10px 14px; border-radius: 6px; margin: 12px 0; }
        .flash-success { background: #e6f4ea; color: #1a7f37; }
        .errors { background: #fde8e8; color: #d1242f; padding: 10px 14px; border-radius: 6px; margin: 12px 0; }
        .errors ul { margin: 6px 0 0; padding-left: 20px; }
        dialog { border: none; border-radius: 10px; padding: 0; max-width: 560px; width: 92%; box-shadow: 0 10px 40px rgba(0,0,0,.2); }
        dialog::backdrop { background: rgba(0,0,0,.4); }
        .modal-head { padding: 16px 20px; border-bottom: 1px solid #eee; font-size: 18px; font-weight: bold; }
        .modal-body { padding: 16px 20px; }
        .modal-foot { padding: 14px 20px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .url-row { display: flex; gap: 8px; margin-bottom: 8px; }
        .url-row input { flex: 1; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        .url-remove { background: #d1242f; color: #fff; border: none; border-radius: 6px; width: 36px; cursor: pointer; }
        .link-add { background: none; border: 1px dashed #0066cc; color: #0066cc; padding: 8px; border-radius: 6px; cursor: pointer; width: 100%; }
        .checkbox-cell { margin-bottom: 8px; }
    </style>
</head>
<body>
    @yield('content')
    @yield('scripts')
</body>
</html>
