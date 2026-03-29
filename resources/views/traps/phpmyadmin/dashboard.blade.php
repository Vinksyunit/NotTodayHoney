<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $server }} | phpMyAdmin</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; }
        body {
            background: #f3f3f4;
            color: #333;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
        }
        a { color: #456798; text-decoration: none; }
        a:hover { text-decoration: underline; color: #2d4467; }

        /* Top navigation */
        #pma_navigation_header {
            background: #fff;
            border-bottom: 1px solid #ccc;
            display: flex;
            align-items: center;
            height: 40px;
            left: 0;
            padding: 0 10px;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 200;
            gap: 2px;
        }
        .logo { color: #456798; font-size: 18px; font-weight: bold; letter-spacing: -0.5px; margin-right: 16px; white-space: nowrap; }
        .logo span { color: #f90; }
        .nav-tabs { display: flex; gap: 0; height: 100%; }
        .nav-tab {
            align-items: center;
            border-bottom: 2px solid transparent;
            color: #555;
            display: flex;
            font-size: 12px;
            height: 100%;
            padding: 0 10px;
            white-space: nowrap;
        }
        .nav-tab:hover { background: #f5f5f5; color: #333; text-decoration: none; }
        .nav-tab.active { border-bottom-color: #456798; color: #456798; font-weight: 600; }
        .nav-right { align-items: center; display: flex; gap: 10px; margin-left: auto; font-size: 12px; color: #777; }
        .nav-right a { color: #456798; }

        /* Left sidebar */
        #pma_navigation {
            background: #fff;
            border-right: 1px solid #ccc;
            bottom: 0;
            left: 0;
            overflow-y: auto;
            position: fixed;
            top: 40px;
            width: 200px;
        }
        .nav-search {
            border-bottom: 1px solid #eee;
            padding: 8px;
        }
        .nav-search input {
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 12px;
            padding: 4px 8px;
            width: 100%;
        }
        .nav-section-title {
            color: #999;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 10px 10px 4px;
            text-transform: uppercase;
        }
        .nav-db-item {
            align-items: center;
            color: #333;
            display: flex;
            font-size: 12px;
            gap: 6px;
            padding: 5px 10px;
        }
        .nav-db-item:hover { background: #f0f4f8; text-decoration: none; }
        .nav-db-item .db-icon { color: #456798; font-size: 14px; }
        .nav-db-item.system { color: #999; }
        .nav-db-item.system .db-icon { color: #bbb; }

        /* Main content */
        #pma_content {
            margin-left: 200px;
            padding-top: 40px;
            min-height: 100vh;
        }
        #pma_main_container { padding: 16px; }
        .pma-page-title {
            align-items: center;
            border-bottom: 1px solid #ddd;
            display: flex;
            gap: 8px;
            margin: 0 0 16px;
            padding-bottom: 10px;
        }
        .pma-page-title h1 { font-size: 18px; font-weight: 400; margin: 0; color: #456798; }
        .server-badge {
            background: #e8eef5;
            border-radius: 3px;
            color: #456798;
            font-size: 11px;
            padding: 2px 6px;
        }

        /* Info panels */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 16px; }
        .info-panel {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .info-panel .panel-header {
            background: #f0f0f0;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 12px;
        }
        .info-panel .panel-body { padding: 10px 12px; }
        .info-row { display: flex; justify-content: space-between; font-size: 12px; padding: 3px 0; border-bottom: 1px solid #f5f5f5; }
        .info-row:last-child { border-bottom: none; }
        .info-row .info-label { color: #666; }
        .info-row .info-value { color: #333; font-family: monospace; }

        /* SQL bar */
        .sql-bar {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-bottom: 14px;
            padding: 10px 12px;
        }
        .sql-bar label { color: #555; font-size: 12px; font-weight: 600; margin-bottom: 6px; display: block; }
        .sql-bar textarea {
            border: 1px solid #ccc;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
            padding: 6px 8px;
            resize: vertical;
            width: 100%;
            height: 60px;
        }
        .sql-bar .sql-actions { display: flex; gap: 8px; margin-top: 6px; align-items: center; }
        .btn { border-radius: 3px; cursor: pointer; font-family: inherit; font-size: 12px; padding: 5px 12px; border: 1px solid; }
        .btn-primary { background: #456798; border-color: #365480; color: #fff; }
        .btn-primary:hover { background: #365480; }
        .btn-secondary { background: #f5f5f5; border-color: #ccc; color: #333; }
        .btn-secondary:hover { background: #e8e8e8; }

        #pma_footer { color: #aaa; font-size: 11px; padding: 12px 16px; border-top: 1px solid #ddd; margin-top: 16px; background: #fff; }
    </style>
</head>
<body>

<div id="pma_navigation_header">
    <span class="logo">php<span>My</span>Admin</span>
    <div class="nav-tabs">
        <a class="nav-tab active" href="#">Databases</a>
        <a class="nav-tab" href="#">SQL</a>
        <a class="nav-tab" href="#">Status</a>
        <a class="nav-tab" href="#">User accounts</a>
        <a class="nav-tab" href="#">Export</a>
        <a class="nav-tab" href="#">Import</a>
        <a class="nav-tab" href="#">Settings</a>
        <a class="nav-tab" href="#">Replication</a>
        <a class="nav-tab" href="#">Variables</a>
        <a class="nav-tab" href="#">More &rsaquo;</a>
    </div>
    <div class="nav-right">
        <span>Server: <strong>{{ $server }}</strong></span>
        <a href="#">[ Logout ]</a>
    </div>
</div>

<div id="pma_navigation">
    <div class="nav-search">
        <input type="text" placeholder="Search databases...">
    </div>
    <div class="nav-section-title">Databases</div>
    <a class="nav-db-item" href="#"><span class="db-icon">⊟</span> information_schema</a>
    <a class="nav-db-item" href="#"><span class="db-icon">⊟</span> mysql</a>
    <a class="nav-db-item" href="#"><span class="db-icon">⊟</span> myapp_production</a>
    <a class="nav-db-item" href="#"><span class="db-icon">⊟</span> myapp_staging</a>
    <a class="nav-db-item system" href="#"><span class="db-icon">⊟</span> performance_schema</a>
    <a class="nav-db-item system" href="#"><span class="db-icon">⊟</span> sys</a>
</div>

<div id="pma_content">
    <div id="pma_main_container">
        <div class="pma-page-title">
            <h1>General settings</h1>
            <span class="server-badge">{{ $server }}</span>
        </div>

        <div class="sql-bar">
            <label>Run SQL query/queries on server "{{ $server }}":</label>
            <textarea name="sql_query" placeholder="SELECT * FROM ..."></textarea>
            <div class="sql-actions">
                <button class="btn btn-primary">Go</button>
                <button class="btn btn-secondary">Format</button>
                <label style="font-size:12px;color:#666;margin:0;">
                    <input type="checkbox"> Retain query box
                </label>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-panel">
                <div class="panel-header">Database server</div>
                <div class="panel-body">
                    <div class="info-row"><span class="info-label">Server:</span><span class="info-value">{{ $server }} via TCP/IP</span></div>
                    <div class="info-row"><span class="info-label">Server type:</span><span class="info-value">MySQL</span></div>
                    <div class="info-row"><span class="info-label">Server version:</span><span class="info-value">8.0.35 - MySQL Community</span></div>
                    <div class="info-row"><span class="info-label">Protocol version:</span><span class="info-value">10</span></div>
                    <div class="info-row"><span class="info-label">User:</span><span class="info-value">root@{{ $server }}</span></div>
                    <div class="info-row"><span class="info-label">Character set:</span><span class="info-value">UTF-8 Unicode (utf8mb4)</span></div>
                </div>
            </div>

            <div class="info-panel">
                <div class="panel-header">Web server</div>
                <div class="panel-body">
                    <div class="info-row"><span class="info-label">Apache version:</span><span class="info-value">2.4.58</span></div>
                    <div class="info-row"><span class="info-label">PHP extension:</span><span class="info-value">mysqli</span></div>
                    <div class="info-row"><span class="info-label">PHP version:</span><span class="info-value">8.2.12</span></div>
                </div>
            </div>

            <div class="info-panel">
                <div class="panel-header">phpMyAdmin</div>
                <div class="panel-body">
                    <div class="info-row"><span class="info-label">Version:</span><span class="info-value">{{ $version }}</span></div>
                    <div class="info-row"><span class="info-label">Documentation:</span><span class="info-value"><a href="#">phpMyAdmin docs</a></span></div>
                    <div class="info-row"><span class="info-label">Wiki:</span><span class="info-value"><a href="#">phpMyAdmin wiki</a></span></div>
                    <div class="info-row"><span class="info-label">Official site:</span><span class="info-value"><a href="#">phpmyadmin.net</a></span></div>
                    <div class="info-row"><span class="info-label">Settings storage:</span><span class="info-value" style="color:#888;">Not configured</span></div>
                </div>
            </div>
        </div>
    </div>
    <div id="pma_footer">
        phpMyAdmin {{ $version }} &mdash; <a href="https://www.phpmyadmin.net" target="_blank" rel="noopener">www.phpmyadmin.net</a>
    </div>
</div>

</body>
</html>
