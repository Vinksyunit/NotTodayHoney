<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Dashboard — {{ $title }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; }
        body {
            background: #f4f6fb;
            color: #333;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 14px;
        }
        a { color: #667eea; text-decoration: none; }
        a:hover { text-decoration: underline; }

        /* Sidebar */
        #sidebar {
            background: #1a1a2e;
            bottom: 0;
            color: #a0aec0;
            left: 0;
            position: fixed;
            top: 0;
            width: 220px;
        }
        .sidebar-brand {
            align-items: center;
            border-bottom: 1px solid #2d2d4e;
            display: flex;
            gap: 10px;
            height: 60px;
            padding: 0 20px;
        }
        .sidebar-brand .brand-icon {
            align-items: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 8px;
            color: #fff;
            display: flex;
            font-size: 16px;
            font-weight: 700;
            height: 32px;
            justify-content: center;
            width: 32px;
        }
        .sidebar-brand .brand-name { color: #fff; font-size: 15px; font-weight: 600; }
        .sidebar-section { color: #4a5568; font-size: 10px; font-weight: 700; letter-spacing: 1px; padding: 18px 20px 6px; text-transform: uppercase; }
        .sidebar-item {
            align-items: center;
            color: #a0aec0;
            display: flex;
            font-size: 13px;
            gap: 10px;
            padding: 9px 20px;
        }
        .sidebar-item:hover { background: #2d2d4e; color: #fff; text-decoration: none; }
        .sidebar-item.active { background: #2d2d4e; border-left: 3px solid #667eea; color: #fff; padding-left: 17px; }
        .sidebar-item .item-icon { font-size: 15px; width: 18px; text-align: center; }
        .sidebar-divider { border-top: 1px solid #2d2d4e; margin: 8px 0; }

        /* Top bar */
        #topbar {
            align-items: center;
            background: #fff;
            border-bottom: 1px solid #e8ecf4;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
            display: flex;
            height: 60px;
            left: 220px;
            padding: 0 24px;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 100;
        }
        .topbar-title { color: #1a1a2e; font-size: 18px; font-weight: 600; }
        .topbar-right { align-items: center; display: flex; gap: 16px; margin-left: auto; }
        .topbar-badge {
            align-items: center;
            background: #667eea;
            border-radius: 12px;
            color: #fff;
            display: flex;
            font-size: 11px;
            padding: 2px 8px;
        }
        .topbar-avatar {
            align-items: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            color: #fff;
            display: flex;
            font-size: 13px;
            font-weight: 700;
            height: 34px;
            justify-content: center;
            width: 34px;
        }
        .topbar-user { color: #555; font-size: 13px; }

        /* Main content */
        #main {
            margin-left: 220px;
            padding: 84px 24px 24px;
            min-height: 100vh;
        }

        /* Stat cards */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: #fff;
            border: 1px solid #e8ecf4;
            border-radius: 10px;
            padding: 20px;
        }
        .stat-card .stat-label { color: #888; font-size: 12px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase; margin-bottom: 8px; }
        .stat-card .stat-value { color: #1a1a2e; font-size: 28px; font-weight: 700; margin-bottom: 4px; }
        .stat-card .stat-change { font-size: 12px; }
        .stat-card .stat-change.up { color: #38a169; }
        .stat-card .stat-change.down { color: #e53e3e; }
        .stat-card .stat-icon { float: right; font-size: 28px; opacity: .15; margin-top: -36px; }

        /* Content panels */
        .panel-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; }
        .panel {
            background: #fff;
            border: 1px solid #e8ecf4;
            border-radius: 10px;
            overflow: hidden;
        }
        .panel-header {
            align-items: center;
            border-bottom: 1px solid #e8ecf4;
            display: flex;
            justify-content: space-between;
            padding: 14px 18px;
        }
        .panel-header h3 { color: #1a1a2e; font-size: 14px; font-weight: 600; margin: 0; }
        .panel-header a { color: #667eea; font-size: 12px; }
        .panel-body { padding: 0; }

        /* Table */
        table { border-collapse: collapse; width: 100%; }
        th { background: #f9fafc; border-bottom: 1px solid #e8ecf4; color: #888; font-size: 11px; font-weight: 600; letter-spacing: .5px; padding: 10px 18px; text-align: left; text-transform: uppercase; }
        td { border-bottom: 1px solid #f0f2f8; color: #444; font-size: 13px; padding: 12px 18px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafbff; }
        .badge { border-radius: 4px; font-size: 11px; font-weight: 600; padding: 2px 8px; }
        .badge-success { background: #e6fffa; color: #2f855a; }
        .badge-warning { background: #fffaf0; color: #c05621; }
        .badge-danger  { background: #fff5f5; color: #c53030; }

        /* Activity list */
        .activity-item { border-bottom: 1px solid #f0f2f8; padding: 12px 18px; }
        .activity-item:last-child { border-bottom: none; }
        .activity-item .activity-actor { color: #1a1a2e; font-weight: 600; font-size: 13px; }
        .activity-item .activity-desc { color: #666; font-size: 12px; margin-top: 2px; }
        .activity-item .activity-time { color: #aaa; font-size: 11px; float: right; }
    </style>
</head>
<body>

<div id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">{{ mb_substr($title, 0, 1) }}</div>
        <span class="brand-name">{{ $title }}</span>
    </div>
    <div class="sidebar-section">Main</div>
    <a class="sidebar-item active" href="#"><span class="item-icon">⊞</span> Dashboard</a>
    <a class="sidebar-item" href="#"><span class="item-icon">👥</span> Users</a>
    <a class="sidebar-item" href="#"><span class="item-icon">📦</span> Products</a>
    <a class="sidebar-item" href="#"><span class="item-icon">📋</span> Orders</a>
    <a class="sidebar-item" href="#"><span class="item-icon">📊</span> Reports</a>
    <div class="sidebar-divider"></div>
    <div class="sidebar-section">System</div>
    <a class="sidebar-item" href="#"><span class="item-icon">⚙</span> Settings</a>
    <a class="sidebar-item" href="#"><span class="item-icon">🔒</span> Security</a>
    <a class="sidebar-item" href="#"><span class="item-icon">🔑</span> API Keys</a>
    <div class="sidebar-divider"></div>
    <a class="sidebar-item" href="{{ $loginPath }}"><span class="item-icon">↩</span> Sign out</a>
</div>

<div id="topbar">
    <span class="topbar-title">Dashboard</span>
    <div class="topbar-right">
        <span class="topbar-badge">3 alerts</span>
        <span class="topbar-user">admin</span>
        <div class="topbar-avatar">A</div>
    </div>
</div>

<div id="main">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">4,821</div>
            <div class="stat-change up">&#9650; 12% this month</div>
            <div class="stat-icon">👥</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Revenue</div>
            <div class="stat-value">$38.4k</div>
            <div class="stat-change up">&#9650; 8.3% this month</div>
            <div class="stat-icon">💰</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Sessions</div>
            <div class="stat-value">143</div>
            <div class="stat-change down">&#9660; 3% today</div>
            <div class="stat-icon">🖥</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Open Tickets</div>
            <div class="stat-value">27</div>
            <div class="stat-change up">&#9650; 5 new</div>
            <div class="stat-icon">🎫</div>
        </div>
    </div>

    <div class="panel-grid">
        <div class="panel">
            <div class="panel-header">
                <h3>Recent Orders</h3>
                <a href="#">View all</a>
            </div>
            <div class="panel-body">
                <table>
                    <thead>
                        <tr><th>ID</th><th>Customer</th><th>Amount</th><th>Date</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>#10042</td><td>Alice Martin</td><td>$149.00</td><td>Mar 28, 2026</td><td><span class="badge badge-success">Completed</span></td></tr>
                        <tr><td>#10041</td><td>Bob Chen</td><td>$79.50</td><td>Mar 27, 2026</td><td><span class="badge badge-warning">Pending</span></td></tr>
                        <tr><td>#10040</td><td>Carol White</td><td>$320.00</td><td>Mar 26, 2026</td><td><span class="badge badge-success">Completed</span></td></tr>
                        <tr><td>#10039</td><td>David Kim</td><td>$55.00</td><td>Mar 25, 2026</td><td><span class="badge badge-danger">Refunded</span></td></tr>
                        <tr><td>#10038</td><td>Emma Jones</td><td>$210.00</td><td>Mar 24, 2026</td><td><span class="badge badge-success">Completed</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h3>Recent Activity</h3>
            </div>
            <div class="panel-body">
                <div class="activity-item">
                    <span class="activity-time">2 min ago</span>
                    <div class="activity-actor">admin</div>
                    <div class="activity-desc">Updated system settings</div>
                </div>
                <div class="activity-item">
                    <span class="activity-time">1 hr ago</span>
                    <div class="activity-actor">john.doe</div>
                    <div class="activity-desc">Created new user account</div>
                </div>
                <div class="activity-item">
                    <span class="activity-time">3 hr ago</span>
                    <div class="activity-actor">admin</div>
                    <div class="activity-desc">Exported user data (CSV)</div>
                </div>
                <div class="activity-item">
                    <span class="activity-time">Yesterday</span>
                    <div class="activity-actor">system</div>
                    <div class="activity-desc">Automated backup completed</div>
                </div>
                <div class="activity-item">
                    <span class="activity-time">Yesterday</span>
                    <div class="activity-actor">jane.smith</div>
                    <div class="activity-desc">Resolved 4 support tickets</div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
