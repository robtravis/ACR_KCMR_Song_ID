<?php

// Loop indefinitely
while (true) {
    $project_id = "YOUR_PROJECT_ID";
    $stream_id = "YOUR_STREAM_ID";
    $token = "XXXX"; // Replace with your actual access token

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "Accept: application/json\r\n" .
                "Authorization: Bearer " . $token . "\r\n"
        ]
    ];
    $context = stream_context_create($opts);

    $data = file_get_contents("https://api-v2.acrcloud.com/api/bm-cs-projects/" . $project_id . "/streams/" . $stream_id . "/results?type=last", false, $context);

    $obj = json_decode($data, true);
    $title = "";
    $artists = "";

    if (isset($obj['data']) && count($obj['data']) > 0) {
        $metadata = $obj['data'][0]['metadata'];
        if (array_key_exists('music', $metadata)) {
            $music = $metadata['music'][0];
            $title = $music['title'];
            $arlist = [];
            if (array_key_exists('artists', $music)) {
                foreach ($music['artists'] as $ar) {
                    $arlist[] = $ar['name'];
                }
            }
            if (count($arlist) > 0) {
                $artists = implode(', ', $arlist);
            }
        }
    }

    // Create the formatted string without "Now Playing" prefix
    $new_song = $title . " - " . $artists;

    // Path to the nowplaying.txt file
    $file_path = "PATH_TO_NOWPLAYING.TXT";

    // Read the last song from the file to avoid unnecessary writes
    $last_song = "";
    if (file_exists($file_path)) {
        $last_song = file_get_contents($file_path);
    }

    // Write to file if the song has changed
    if ($new_song !== $last_song) {
        file_put_contents($file_path, $new_song);
        echo "Updated Now Playing: $new_song\n";
    } else {
        echo "No change in the currently playing song.\n";
    }

    // Print the current song info for reference
    $ret = ['title' => $title, 'artists' => $artists, 'result' => $obj];
    print_r(json_encode($ret));

    // Wait for 20 seconds before checking again
    sleep(20);
}

?>