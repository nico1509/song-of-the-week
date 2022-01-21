<?php

class SongOfTheWeekLoader
{
    private const PLAYLIST_API = 'https://api.spotify.com/v1/playlists';
    private const AUTH_API = 'https://accounts.spotify.com/api/token';
    private const AT_FILE = 'access-token.txt';
    private const PLAYLIST_FILE = 'on-repeat.json';
    private const SOTW_FILE = 'song-of-the-week.txt';
    private const SOTW_URL_FILE = 'song-of-the-week-url.txt';

    private string $onRepeatPlaylistId;
    private string $dataDir;
    private string $spotifyRefreshToken;
    private string $spotifyClientId;
    private string $spotifyClientSecret;

    public function __construct(
        string $onRepeatPlaylistId,
        string $dataDir,
        string $spotifyRefreshToken,
        string $spotifyClientId,
        string $spotifyClientSecret
    )
    {
        $this->onRepeatPlaylistId = $onRepeatPlaylistId;
        $this->dataDir = $dataDir;
        $this->spotifyRefreshToken = $spotifyRefreshToken;
        $this->spotifyClientId = $spotifyClientId;
        $this->spotifyClientSecret = $spotifyClientSecret;
    }

    public function getCurrentAccessToken(): string
    {
        return file_get_contents($this->dataDir . '/' . self::AT_FILE);
    }

    public function renewAccessToken(): ?string
    {
        $curl = curl_init();

        $header = [];
        $header[] = 'Authorization: Basic ' . base64_encode($this->spotifyClientId . ':' . $this->spotifyClientSecret);

        $formData = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->spotifyRefreshToken,
        ];

        curl_setopt($curl, CURLOPT_URL, self::AUTH_API);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        curl_close($curl);

        $accessToken = json_decode($result, true)['access_token'];
        file_put_contents($this->dataDir . '/' . self::AT_FILE, $accessToken);

        return $accessToken;
    }

    public function loadSongOfTheWeek(string $accessToken): ?string
    {
        if (!$this->downloadPlaylist($accessToken)) {
            return null;
        }
        return $this->saveSongData();
    }

    protected function downloadPlaylist(string $accessToken): bool
    {
        $curl = curl_init();

        $header = [];
        $header[] = 'Authorization: Bearer ' . $accessToken;
        $header[] = 'Content-Type: application/json';

        curl_setopt($curl, CURLOPT_URL, self::PLAYLIST_API . '/' . $this->onRepeatPlaylistId);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        curl_close($curl);

        $resultData = json_decode($result, true);

        if (!isset($resultData['tracks'])) {
            return false;
        }

        file_put_contents($this->dataDir . '/' . self::PLAYLIST_FILE, $result);

        return true;
    }

    protected function saveSongData(): string
    {
        $playlistJson = file_get_contents($this->dataDir . '/' . self::PLAYLIST_FILE);

        $playlist = json_decode($playlistJson, true);
    
        $numberOfTracks = count($playlist['tracks']['items']);
        $randomTrackId = 0; //mt_rand(0, $numberOfTracks - 1);
    
        $trackName = $playlist['tracks']['items'][$randomTrackId]['track']['name'];
        $trackLink = $playlist['tracks']['items'][$randomTrackId]['track']['external_urls']['spotify'];
        $artist = $playlist['tracks']['items'][$randomTrackId]['track']['artists'][0]['name'];

        $sotwText = $artist . ' - ' . $trackName;
        file_put_contents($this->dataDir . '/' . self::SOTW_FILE, $sotwText);
        file_put_contents($this->dataDir . '/' . self::SOTW_URL_FILE, $trackLink);
        return $sotwText . '(' . $trackLink . ')';
    }

}

