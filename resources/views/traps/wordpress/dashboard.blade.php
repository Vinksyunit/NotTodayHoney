<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard &lsaquo; {{ $siteName }} &mdash; WordPress</title>
    <style type="text/css">
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; height: 100%; }
        body {
            background: #f0f0f1;
            color: #3c434a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            font-size: 13px;
        }
        a { color: #2271b1; text-decoration: none; }
        a:hover { color: #135e96; }

        /* Admin bar */
        #wpadminbar {
            background: #1d2327;
            color: #a7aaad;
            display: flex;
            align-items: center;
            height: 32px;
            left: 0;
            padding: 0 8px;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 99999;
            gap: 4px;
        }
        #wpadminbar .ab-wp-logo { padding: 0 8px; opacity: 0.7; }
        #wpadminbar .ab-item {
            color: #a7aaad;
            display: flex;
            align-items: center;
            height: 32px;
            padding: 0 8px;
            font-size: 13px;
            gap: 4px;
        }
        #wpadminbar .ab-item:hover { background: #2c3338; color: #fff; }
        #wpadminbar .ab-new { background: #2271b1; border-radius: 2px; color: #fff; padding: 3px 8px; font-size: 12px; margin-left: 4px; }
        #wpadminbar .ab-new:hover { background: #135e96; }
        #wpadminbar .ab-right { margin-left: auto; display: flex; align-items: center; }
        #wpadminbar .ab-avatar {
            width: 24px; height: 24px; border-radius: 50%; background: #50575e;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 11px; font-weight: 600; margin-right: 6px;
        }

        /* Sidebar */
        #adminmenuwrap { background: #1d2327; bottom: 0; left: 0; position: fixed; top: 32px; width: 160px; }
        #adminmenu { list-style: none; margin: 0; padding: 8px 0; }
        #adminmenu li a {
            color: #a7aaad;
            display: block;
            font-size: 13px;
            overflow: hidden;
            padding: 8px 12px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        #adminmenu li a:hover { background: #2c3338; color: #fff; }
        #adminmenu li.current a { background: #2c3338; border-left: 3px solid #2271b1; color: #fff; padding-left: 9px; }
        #adminmenu .menu-icon { display: inline-block; margin-right: 6px; opacity: 0.7; text-align: center; width: 18px; }
        #adminmenu li.current .menu-icon { opacity: 1; }
        #adminmenu .separator { border-top: 1px solid #2c3338; margin: 6px 0; }

        /* Content */
        #wpcontent { margin-left: 160px; min-height: 100vh; padding-top: 32px; }
        #wpbody-content { padding: 20px 20px 0; }
        .wrap h1 { color: #1d2327; font-size: 23px; font-weight: 400; line-height: 1.3; margin: 0 0 20px; }

        /* Notice */
        .notice {
            background: #fff;
            border-left: 4px solid #72aee6;
            border-radius: 0 4px 4px 0;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            margin: 0 0 16px;
            padding: 10px 14px;
        }
        .notice p { margin: 0; }

        /* Widgets */
        #dashboard-widgets { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 8px; }
        .postbox { background: #fff; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
        .postbox .postbox-header {
            align-items: center;
            border-bottom: 1px solid #c3c4c7;
            display: flex;
            height: 42px;
            padding: 0 12px;
        }
        .postbox .postbox-header h2 { color: #1d2327; font-size: 14px; font-weight: 600; margin: 0; }
        .postbox .inside { padding: 12px; }

        /* At a glance */
        .at-a-glance-list { display: grid; grid-template-columns: 1fr 1fr; list-style: none; margin: 0; padding: 0; }
        .at-a-glance-list li { border-bottom: 1px solid #f0f0f1; padding: 8px 4px; }
        .at-a-glance-list li a { align-items: center; color: #3c434a; display: flex; gap: 6px; }
        .at-a-glance-list li a:hover { color: #2271b1; }
        .at-a-glance-list .count { font-size: 20px; font-weight: 400; }
        .at-a-glance-wp { border-top: 1px solid #f0f0f1; color: #646970; font-size: 12px; margin-top: 8px; padding-top: 8px; }

        /* Quick draft */
        .quick-draft label { color: #1d2327; display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; }
        .quick-draft input[type="text"],
        .quick-draft textarea {
            border: 1px solid #8c8f94;
            border-radius: 4px;
            color: #2c3338;
            display: block;
            font-family: inherit;
            font-size: 13px;
            margin-bottom: 10px;
            padding: 6px 8px;
            width: 100%;
        }
        .quick-draft textarea { height: 80px; resize: vertical; }
        .quick-draft .submit-btn { background: #2271b1; border: none; border-radius: 3px; color: #fff; cursor: pointer; font-size: 13px; padding: 4px 12px; }

        /* Activity */
        .activity-block { margin-bottom: 12px; }
        .activity-block h3 { color: #646970; font-size: 12px; font-weight: 600; letter-spacing: .5px; margin: 0 0 8px; text-transform: uppercase; }
        .activity-item { border-bottom: 1px solid #f0f0f1; display: flex; font-size: 12px; justify-content: space-between; padding: 6px 0; }
        .activity-item:last-child { border-bottom: none; }
        .activity-item .activity-date { color: #646970; white-space: nowrap; }
    </style>
</head>
<body class="wp-admin wp-core-ui">

<div id="wpadminbar">
    <div class="ab-wp-logo">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="20" height="20" fill="#a7aaad" aria-hidden="true">
            <path d="M10 2c-4.42 0-8 3.58-8 8s3.58 8 8 8 8-3.58 8-8-3.58-8-8-8zm5.93 8c0 .45-.05.88-.14 1.3L13 10.82l-1.62-4.55c.27-.01.51-.04.51-.04.24-.03.21-.38-.03-.36 0 0-.73.06-1.2.06-.44 0-1.18-.06-1.18-.06-.24-.02-.27.34-.03.36 0 0 .22.03.46.04L11.56 11l-1.3 3.9-3.27-9.17c.27-.01.52-.04.52-.04.24-.03.21-.38-.03-.36 0 0-.73.06-1.2.06-.08 0-.18 0-.28-.01C7.28 4.01 8.59 3.4 10 3.4c1.93 0 3.67.78 4.93 2.04-.03 0-.06-.01-.1-.01-.44 0-.75.38-.75.79 0 .37.21.68.44 1.05.17.3.37.68.37 1.23 0 .38-.15.82-.34 1.43l-.44 1.48-1.6-4.76c.27-.01.52-.04.52-.04.24-.03.21-.38-.03-.36zM3.4 10c0-1.26.29-2.45.8-3.51l2.84 7.78A6.6 6.6 0 013.4 10zm6.6 6.6c-.85 0-1.67-.15-2.43-.43l2.58-7.49 2.64 7.23c.01.03.03.06.04.09A6.56 6.56 0 0110 16.6zm3.89-9.14l.01.06 1.33 3.63c.33-.94.51-1.93.51-2.96-.01-.72-.25-1.5-.44-2.08-.1.36-.22.85-.22 1.11l-.49.68-.7-.44z"/>
        </svg>
    </div>
    <a href="{{ $path }}/" class="ab-item">{{ $siteName }}</a>
    <a href="#" class="ab-item"><span class="ab-new">+ New</span></a>
    <div class="ab-right">
        <div class="ab-avatar">A</div>
        <a href="{{ $path }}/profile.php" class="ab-item" style="font-size:12px;">Howdy, admin</a>
    </div>
</div>

<div id="adminmenuwrap">
    <ul id="adminmenu">
        <li class="current"><a href="{{ $path }}/"><span class="menu-icon">⊞</span>Dashboard</a></li>
        <li><a href="#"><span class="menu-icon">✎</span>Posts</a></li>
        <li><a href="#"><span class="menu-icon">⊟</span>Media</a></li>
        <li><a href="#"><span class="menu-icon">☰</span>Pages</a></li>
        <li><a href="#"><span class="menu-icon">✉</span>Comments</a></li>
        <li class="separator"></li>
        <li><a href="#"><span class="menu-icon">◈</span>Appearance</a></li>
        <li><a href="#"><span class="menu-icon">⧉</span>Plugins</a></li>
        <li><a href="#"><span class="menu-icon">👤</span>Users</a></li>
        <li><a href="#"><span class="menu-icon">⚙</span>Tools</a></li>
        <li><a href="#"><span class="menu-icon">⚙</span>Settings</a></li>
    </ul>
</div>

<div id="wpcontent">
    <div id="wpbody-content">
        <div class="wrap">
            <h1>Dashboard</h1>

            <div class="notice">
                <p>WordPress 6.4.2 is available! <a href="#">Please update now</a>.</p>
            </div>

            <div id="dashboard-widgets">
                <div class="postbox">
                    <div class="postbox-header"><h2>At a Glance</h2></div>
                    <div class="inside">
                        <ul class="at-a-glance-list">
                            <li><a href="#"><span class="count">3</span> Posts</a></li>
                            <li><a href="#"><span class="count">2</span> Pages</a></li>
                            <li><a href="#"><span class="count">1</span> Comment</a></li>
                            <li><a href="#"><span class="count">0</span> Pending</a></li>
                        </ul>
                        <p class="at-a-glance-wp">WordPress 6.4.2 &middot; <a href="#">Twenty Twenty-Four</a> theme</p>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header"><h2>Quick Draft</h2></div>
                    <div class="inside quick-draft">
                        <label for="post-title">Title</label>
                        <input type="text" id="post-title" name="post_title" placeholder="Title">
                        <label for="post-content">Content</label>
                        <textarea id="post-content" name="content" placeholder="What's on your mind?"></textarea>
                        <button class="submit-btn">Save Draft</button>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header"><h2>Activity</h2></div>
                    <div class="inside">
                        <div class="activity-block">
                            <h3>Recently Published</h3>
                            <div class="activity-item">
                                <a href="#">Hello world!</a>
                                <span class="activity-date">Jan 1, 12:00 am</span>
                            </div>
                        </div>
                        <div class="activity-block">
                            <h3>Recent Comments</h3>
                            <div class="activity-item">
                                <a href="#">Mr WordPress on Hello world!</a>
                                <span class="activity-date">Jan 1</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header"><h2>WordPress News</h2></div>
                    <div class="inside" style="color:#646970; font-size:12px; padding:20px 12px; text-align:center;">
                        Unable to load WordPress news feed.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
