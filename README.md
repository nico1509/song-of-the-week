# song-of-the-week
Load your personal song of the week from spotify and do whatever you want with it

## Prerequisites
Get a client-id, client-secret, refresh-token and the playlist-id of your "On Repeat" playlist from Spotify using their [Developer Docs and Examples](https://developer.spotify.com/documentation/web-api/quick-start/)

## run.php
On the first run, the script creates a config.json where you input your credentials and stuff.
After that it fetches the first track of your "On Repeat" playlist and saves it as a text file in your configured data-dir.
Install run.php as a cron job to run it weekly.
