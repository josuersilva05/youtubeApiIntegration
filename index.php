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
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>
	<title>Document</title>
</head>
<body class="container" ng-app="myApp" ng-controller="customersCtrl">
	   
    <header>
        <h1>Minhas playlists</h1>
    </header>
    <div class="col-md-6">
        <form id="atualizarPlaylist" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <a href="javascript:void(0);" id="atualizarPlaylistLink">Atualizar Playlists</a>
            <input type="hidden" name="frmAtualizar">
        </form>
    </div>
    <section>
        <?php
            include "playlistsUtils.class.php";
            $playlistsObj = new playlistsUtils(); //$playlistsObj->reloadPlaylists(); // teste
            if(isset($_POST["frmAtualizar"])) $playlistsObj->checkUpdateAvailable();
            if(isset($_POST["frmBuscarPlaylists"])){ /* Checa se conseguiu achar alguma playlist */
                $plStatus = $playlistsObj->findPlaylists();
                $strLocationUrl =  "Location: http://localhost:8080/youtubeAPI_playlists/youtubeApiIntegration/index.php?updated=";
                if($plStatus == true) header($strLocationUrl."1");
                else header($strLocationUrl."2");
            }
            if($playlistsObj->loadPlaylists() == false) include "components/buscarPlaylists.php";
            else{
                if (isset($_GET['updated']) and $_GET['updated']  == "1") include "components/index.playlistSaveSuccess.php";
                elseif (isset($_GET['updated']) and $_GET['updated']  == "2") include "components/index.playlistSaveError.php";
                include "components/index.playlistLoadSuccess.php";
            }
        ?>
    </section>
<br>
<br>
<br>
<br>
<br>

<!--<iframe src="https://www.youtube.com/embed/videoseries?list=PL2hQMuIBSUKfanhhpBCee8y7L9CVWMhJy" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>-->

<script>
    var linkAtualizar = document.querySelector("#atualizarPlaylistLink");
    linkAtualizar.addEventListener("click", function(){
        document.getElementById('atualizarPlaylist').submit();
    });
</script>
<script>
console.log('asgasdfa');
    var app = angular.module('myApp', []);
    app.controller('customersCtrl', function($scope, $http) {
        console.log('afdaface');
        $http.get("index.php").then(function success(response) {
            //$scope.names = response.data.records;
            console.log(response);
        }, function error(response){
            console.log(response);    
        });
    });
</script>

</body>
</html>