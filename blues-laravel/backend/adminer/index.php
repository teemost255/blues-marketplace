<?php
// Adminer entry point — runs on port 8080
// Launch with: php -S 0.0.0.0:8080 blues-laravel/backend/adminer/index.php

// Optional: auto-fill connection info from environment
function adminer_object() {
    class AdminerCustom extends Adminer {
        function name() { return 'Blues Marketplace DB Admin'; }
        function loginForm() {
            // Pre-fill server from DATABASE_URL if set
            $url = getenv('DATABASE_URL');
            $server = '';
            if ($url) {
                $parsed = parse_url($url);
                $server = ($parsed['host'] ?? '') . ':' . ($parsed['port'] ?? '5432');
            }
            echo '<input type="hidden" name="auth[driver]" value="pgsql">';
            echo '<table cellspacing="0" class="layout">';
            echo '<tr><th>' . lang('Server') . '</th><td><input name="auth[server]" value="' . htmlspecialchars($server) . '"></td></tr>';
            echo '<tr><th>' . lang('Username') . '</th><td><input name="auth[username]" id="username" value="' . htmlspecialchars(getenv('PGUSER') ?: 'postgres') . '"></td></tr>';
            echo '<tr><th>' . lang('Password') . '</th><td><input type="password" name="auth[password]"></td></tr>';
            echo '<tr><th>' . lang('Database') . '</th><td><input name="auth[db]" value="' . htmlspecialchars(getenv('PGDATABASE') ?: '') . '"></td></tr>';
            echo '</table>';
            echo script("focus(qs('#username'));");
            echo '<p><input type="submit" value="' . lang('Login') . '"></p>';
        }
    }
    return new AdminerCustom;
}

include __DIR__ . '/adminer.php';
