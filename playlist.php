<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <title>Document</title>
</head>
<body class="container">
    <?php
        $playlistId = $_GET['playlistId'];
        $itemId = $_GET['item'];

        require_once('videosUtils.class.php');
        $videoUtils = new VideosUtils();
        if($videoUtils->loadVideos($itemId) == true){
            echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>Videos carregados!
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                    </div>";
        } else{ //echo "Nenhum vídeo encontrado";
            $videoUtils->searchForVideosByPlaylist($itemId,$playlistId);
            
            //Procurar vídeos na playlist se não forem encontrados nenhum no banco
            /*echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Não foram encontrados nenhum video registrado.
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
            </button>
            </div>";*/
        }

    ?>


</body>
</html>