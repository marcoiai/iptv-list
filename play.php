<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
  <title>IP TV Personalizador</title>

  <!-- CSS  -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mpegts.js@latest"></script>
    <style>
        .card-image img {
            width: 150px;
            height: 150px !important;
        }
    </style>
</head>
<body>
  <nav class="light-blue lighten-1" role="navigation">
    <div class="nav-wrapper container"><a id="logo-container" href="#" class="brand-logo">IP TV Personalizador</a>
      <ul class="right hide-on-med-and-down">
        <li><a href="#">Navbar Link</a></li>
      </ul>

      <ul id="nav-mobile" class="sidenav">
        <li><a href="#">Navbar Link</a></li>
      </ul>
      <a href="#" data-target="nav-mobile" class="sidenav-trigger"><i class="material-icons">menu</i></a>
    </div>
  </nav>
  <div class="section no-pad-bot" id="index-banner">
    <div class="container">
      <br><br>
      <h1 class="header center orange-text">Selecione os canais que deseja contratar!</h1>
      <div class="row center">
        <h5 class="header col s12 light">Revise a lista e se desejar, pode clical em "Selecionar Todos" para facilitar.</h5>
      </div>
      <div class="row center">
        <div class="input-field col s6">
          <input placeholder="Choose a filename for the new list" id="filename" name="filename" type="text" class="validate">
          <label for="filename">File Name</label>
        </div>
      </div>
      <div class="row center">
        <form method="post" action="http://127.0.0.1:9090/play.php?start=0&stop=100000">
        <div class="input-field col s6">
          <input placeholder="Digite os generos desejados" id="generos" name="generos" type="text" class="generos">
          <label for="filename">Generos</label>
        </div>
        <div class="input-field col s6">
          <input placeholder="Digite os generos desejados" id="especifico" name="especifico" type="text" class="especifico">
          <label for="filename">Especifico</label>
        </div>
        <button type="submit" id="filter" class="btn-large waves-effect waves-light orange">Filtrar</button>
        </form>
      </div>
      <br><br>

    </div>
  </div>

  <div class="container">

      <!--   Icon Section   -->
      <div class="row center">
        <div class="col s12 m12">
          <div class="icon-block">
            <h2 class="center light-blue-text"><i class="material-icons">movie</i></h2>
            <h5 class="center">Aqui seu vídeo será exibido.</h5>

            <div class="video-container container-other" hide>
                <iframe id="video-player" width="853" height="480" src="//www.youtube.com/embed/Q8TXgCzxEnw?rel=0" frameborder="0" allowfullscreen></iframe>
            </div>

            <div class="video-container container-ts" hide>
                <!--
                <input type="text" id="streamInput" placeholder="Enter .ts live stream URL (e.g., http://yourserver.com/live.ts)">
                <button id="loadStreamBtn">Load Stream</button>
                --->

                <video id="videoPlayer-ts" controls autoplay></video>
                <p class="message" id="errorMessage"></p>
            </div>

            <script>
            const videoElement = document.getElementById('videoPlayer-ts');
            const streamInput = document.getElementById('streamInput');
            const loadStreamBtn = document.getElementById('loadStreamBtn');
            const errorMessage = document.getElementById('errorMessage');

            let player = null; // To hold the mpegts.js player instance

            function loadStream(streamUrl) {
                const tsSource = streamUrl.trim();
                errorMessage.textContent = ''; // Clear previous errors

                if (!tsSource) {
                    errorMessage.textContent = 'Please enter a stream URL.';
                    return;
                }

                // If a player instance already exists, destroy it before creating a new one
                if (player) {
                    player.pause();
                    player.unload();
                    player.destroy();
                    player = null;
                    videoElement.src = ''; // Clear video source
                }

                if (mpegts.getFeatureList().mseLivePlayback) {
                    player = mpegts.createPlayer({
                        type: 'mse', // Indicate Media Source Extensions
                        isLive: true, // Crucial for live stream optimizations
                        url: tsSource,
                        // Optional: adjust live buffer size if needed
                        liveBufferLatency: 0.5 // e.g., 0.5 seconds latency
                    });

                    player.attachMediaElement(videoElement);
                    player.load();
                    player.play();

                    // Event listeners for debugging and error handling
                    player.on(mpegts.Events.ERROR, function (errorType, errorDetail, errorInfo) {
                        console.error('MPEGTS.js Error:', errorType, errorDetail, errorInfo);

                        errorMessage.textContent = `Stream Error: ${errorType} - ${errorDetail}`;

                        if (errorType === mpegts.ErrorTypes.NETWORK_ERROR) {
                            // Attempt to reload or notify user
                            console.log('Network error, attempting to retry...');
                            // player.load(); // You might attempt to reload, or show a reconnect button
                        }
                    });
                    player.on(mpegts.Events.LOADING_COMPLETE, function () {
                        console.log('Stream loading complete.');
                        //player.load();
                        player.play();
                    });
                    player.on(mpegts.Events.STATISTICS_INFO, function (stats) {
                        // console.log('Statistics:', stats);
                    });
                } else {
                    // Fallback for browsers that might natively support .ts (like Safari)
                    // This is generally less reliable for live .ts streams on non-Safari browsers.
                    if (videoElement.canPlayType('video/mp2t')) { // MIME type for MPEG-TS
                        videoElement.src = tsSource;
                        videoElement.addEventListener('loadedmetadata', function() {
                            videoElement.play();
                        });
                    } else {
                        errorMessage.textContent = 'Your browser does not support playing direct .ts files natively or via mpegts.js.';
                        console.error('Browser does not support direct .ts playback.');
                    }
                }
            }

            //loadStreamBtn.addEventListener('click', loadStream);

            // Optional: Auto-load a default stream on page load for testing
            // streamInput.value = 'http://distribution.phx.cablecast.tv/video/1476/mp2t'; // Example (may not be live/always available)
            // loadStream(); // Uncomment to auto-load
        </script>

            <?php
            ini_set('memory_limit', '6000M');
            ini_set('max_execution_time', 3600);

            if ((isset($argv[0]) && $argv[1] === 'fetch') || isset($_GET['fetch'])) {
                $content = file_get_contents('http://tdnew.shop:8080/get.php?username=marcoaurelio28&password=97638250&type=m3u_plus&output=ts');

                $f = fopen('lista.m3u', 'a+');
                fwrite($f, $content);
                fclose($f);

                echo "Lista gravada\n";
            } else {
                $content = file_get_contents('lista.m3u');
            }

            $start = $argv[1]??$_GET['start'];
            $end = $argv[2]??$_GET['stop'];
            $end += $start;

            $exploded = explode("\r\n", $content);

            if (empty($_POST['generos'])) {
                $excluded_titles = '24 horas|cursos|romance|drama|sexy|filmes|[^canais | filmes]';
                //$included_titles = 'canais|filmes';
                $included_titles = 'canais';
                $included_titles = 'canais|filmes';
            } else {
                $included_titles = $_POST['generos'];
            }

            /* This can serve to include / exclude years
            for ($c = 1990; $c <= 2010; $c++) {
                $excluded_titles .= "|$c";
            }
            */

            //$exploded = explode("\n", $content);
            $exploded_count = count($exploded);

            echo '<form action="post-play.php" method="post" id="form-list">';
            echo <<<INPUT
            <p class="left-align">
                <label>
                <input type="checkbox" id="select_all" name="select_all" />
                <span>Selecionar Tudo</span>
                </label>
            </p>
            INPUT;

            for ($c = $start + 3; $c <= $end; $c++) {

            $original_link = $exploded[$c];

            preg_match('/group-title="([^"]*)/i', $exploded[$c], $titles);

            /*
            if (isset($titles[1])) {
                preg_match_all("/($excluded_titles)+/i", $titles[1], $output_array);

                if (!empty($output_array[0])) {
                    continue;
                }
            }
            */

            if (isset($titles[1])) {
                preg_match_all("/($included_titles)+/i", $titles[1], $output_array);

                if (empty($output_array[0])) {
                    continue;
                }
            }

            preg_match('/tvg-logo="([^"]*)/i', $exploded[$c], $images);

            if (!isset($titles[1])) {
                $titles[1] = "Title";
            }

            $images[1] = $images[1]??"tv.png";

            $encoded = base64_encode("{$original_link}\n");

            $title = explode('http', explode(',', $exploded[$c])[1]);

            if (!empty($_POST['especifico'])) {
                if (stripos($title[0], $_POST['especifico']) === false) {
                    continue;
                }
            }

            echo <<<C
            <div class="col s12 m7">
            <div class="card horizontal">
            <div class="card-image">
                <img src="$images[1]">
            </div>
            <div class="card-stacked">
                <div class="card-content">
                <p>{$titles[1]}</p>
                </div>
                <div class="card-action">
                    <a href="{$title[1]}" class="btn-large waves-effect play-video">$c Play</a>
                    <p>
                    <label>
                        <input class="line" type="checkbox" name="lines[]" value="$encoded" />
                        <span>{$title[0]}</span>
                    </label>
                    </p>
                </div>
            </div>
            </div>
        </div>
C;

            }

            echo '<button type="submit" class="btn-large waves-effect waves-light orange">Salvar</button>';
            echo '</form>';

            echo "100 rows of {$exploded_count}";
            ?>

            <p class="light">We did most of the heavy lifting for you to provide a default stylings that incorporate our custom components. Additionally, we refined animations and transitions to provide a smoother experience for developers.</p>

            <a href="?start=<?php echo $end - 2000 ?>&stop=1000" class="btn-large waves-effect waves-light orange">Previous</a>
            <a href="?start=<?php echo $end ?>&stop=1000" class="btn-large waves-effect waves-light orange">Next</a>
          </div>
        </div>
      </div>

    <br><br>
  </div>

  <footer class="page-footer orange">
    <div class="container">
      <div class="row">
        <div class="col l6 s12">
          <h5 class="white-text">Marco A. Simão</h5>
          <p class="grey-text text-lighten-4">Marco é um Engenheiro de Software com 25+ anos de experiência e muito amor pela profissão. Quando não está desenvolvendo soluções para o público, gosta de assistir filmes / séries, surfar de bodyboard e caminhar / correr.</p>
        </div>
        <div class="col l3 s12">
          <h5 class="white-text">Settings</h5>
          <ul>
            <li><a class="white-text" href="#!">Link 1</a></li>
            <li><a class="white-text" href="#!">Link 2</a></li>
            <li><a class="white-text" href="#!">Link 3</a></li>
            <li><a class="white-text" href="#!">Link 4</a></li>
          </ul>
        </div>
        <div class="col l3 s12">
          <h5 class="white-text">Connect</h5>
          <ul>
            <li><a class="white-text" href="#!">Link 1</a></li>
            <li><a class="white-text" href="#!">Link 2</a></li>
            <li><a class="white-text" href="#!">Link 3</a></li>
            <li><a class="white-text" href="#!">Link 4</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="footer-copyright">
      <div class="container">
      Made by <a class="orange-text text-lighten-3" href="http://materializecss.com">Materialize</a>
      </div>
    </div>
  </footer>

  <script>
    document.getElementById('select_all').addEventListener('click', (e) => {
        document.querySelectorAll('.line').forEach((e) => {
            e.checked = true;
        });
    });

    document.getElementById('form-list').addEventListener('submit', (e) => {
        document.getElementById('form-list').appendChild(document.querySelector('#filename'));
    });

    document.querySelectorAll('.play-video').forEach((e) => {
        e.addEventListener('click', (e) => {
            e.preventDefault();

            if ((e.srcElement.href.split("://")[2]).search('.ts') === -1) {
                document.getElementById('video-player').src = `http://${e.srcElement.href.split("://")[2]}`;
                //console.log(`http://${e.srcElement.href.split("://")[2]}`);

                document.querySelector('.container-other').hidden = false;
                document.querySelector('.container-ts').hidden = true;
            } else {
                //document.getElementById('video-player').src = `http://${e.srcElement.href.split("://")[2]}`;
                //console.log(`http://${e.srcElement.href.split("://")[2]}`);

                loadStream(`http://${e.srcElement.href.split("://")[2]}`);

                document.querySelector('.container-other').hidden = true;
                document.querySelector('.container-ts').hidden = false;
            }
        });
    });
  </script>

  </body>
</html>
