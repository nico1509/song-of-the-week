<?php

$cwd = __DIR__;

require_once($cwd . '/src/SongOfTheWeekLoader.php');

if (!file_exists("$cwd/config.json")) {
    exec("cp $cwd/config.json.example $cwd/config.json");
    print('Enter values in config.json');
    exit(1);
}

$config = json_decode(
    file_get_contents('./config.json'), true
);

$sotwLoader = new SongOfTheWeekLoader(
    $config['on_repeat_playlist_id'],
    $config['data_dir'],
    $config['spotify_refresh_token'],
    $config['spotify_client_id'],
    $config['spotify_client_secret']
);

$accessToken = $sotwLoader->getCurrentAccessToken();
$sotw = $sotwLoader->loadSongOfTheWeek($accessToken);
if ($sotw !== null) {
    print($sotw);
    exit(0);
}

$accessToken = $sotwLoader->renewAccessToken();
if ($accessToken === null) {
    print('Cannot renew access token');
    exit(1);
}
if ($sotw !== null) {
    print($sotw);
    exit(0);
}

print('Unknown error...');
exit(1);