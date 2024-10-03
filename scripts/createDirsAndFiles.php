<?php

if (!is_dir('app/Controllers')) {
    mkdir('app/Controllers', 0755, true);
}
if (!is_dir('app/Models')) {
    mkdir('app/Models', 0755, true);
}
if (!is_dir('config')) {
    mkdir('config', 0755, true);
}

file_put_contents('.env', "DB_HOST=localhost\nDB_NAME=spyframe\nDB_USER=root\nDB_PASS=\n");
