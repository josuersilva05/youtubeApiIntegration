<?php
    class playlistsUtils{

        function findPlaylists(){
            try{
                $key = "AIzaSyCV1o4wQaHEwxxdq9XncfAdj590MrIyMnQ";
                $base_url = "https://www.googleapis.com/youtube/v3/";
                $channelId = "UCSrwdI8538RKP14Aq49Fn4Q";
                $maxResult = 50;
                $API_URL = $base_url."playlists?order=date&part=snippet&channelId=".$channelId."&maxResults=".$maxResult."&key=".$key;
                $playlists = json_decode(file_get_contents($API_URL));

                foreach($playlists->items as $playlist){
                    if($this->registerPlaylist($playlist) == false) 
                        throw new Exception('Não foi possível registrar a playlist: '.$playlist->title);
                }
            } catch(PDOException $e){
                echo "<script>console.log('".$e->getMessage()."')</script>";
            }
        }

        function registerPlaylist($playOBj): bool{
            require_once("dbConnect.php");
            $db = new DbConnect();
            $conn = $db->connect();

            if ($conn == null) return false; 
            try{
                $publish_date = str_replace("T"," ",$playOBj->snippet->publishedAt);
                $publish_date = str_replace("Z"," ",$publish_date);
                if(isset($playOBj->snippet->thumbnails->standard->url)){
                    $thumbnail = $playOBj->snippet->thumbnails->standard->url;
                } else if(isset($playOBj->snippet->thumbnails->high->url)){
                    $thumbnail = $playOBj->snippet->thumbnails->high->url;
                } else if(isset($playOBj->snippet->thumbnails->maxres->url)){
                    $thumbnail = $playOBj->snippet->thumbnails->maxres->url;
                }
                $sql = "call insert_Playlist(:pid, :title, :turl, :pdate, :petag)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":pid", $playOBj->id);
                $stmt->bindParam(":title", $playOBj->snippet->title);
                $stmt->bindParam(":turl", $thumbnail);
                $stmt->bindParam(":pdate", $publish_date);
                $stmt->bindParam(":petag", $playOBj->etag);
                $stmt->execute();
                $db = null;
                return true;
            }
            catch(PDOException $e){
                echo "<script>console.log('".$e->getMessage()."')</script>";
                $db = null;
                return false;
            }
        }

        function loadPlaylists(): bool{
            require_once("dbConnect.php");
            $db = new DbConnect();
            $conn = $db->connect();
            
            try{
                $sql = "SELECT * from playlists";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $playlists = $stmt->fetchAll(PDO::FETCH_ASSOC); //echo "<script>console.log(".$dbPlaylists.")</script>";
                $db = null;
            }
            catch(PDOException $e){
                echo "<span>".$e->getMessage()."</span>";
                $db = null;
                return false;
            }

            if(!isset($playlists) || count($playlists) == 0) return false;
            else{
                echo "<div class='row'>";
                foreach($playlists as $playlist){
                    echo "<div class='col-md-3'>";
                    echo "<div class='thumb-container' id=".$playlist['id'].">
                    <img class='thumb' src=".$playlist['thumbnail_url'].">
                    </div>";
                    echo "<b>".$playlist['title']." ● </b>";
                    echo "<a href='playlist.php?item=".$playlist['id']."&playlistId=".$playlist['playlist_id']."'>Ver videos</a>";    
                    echo "</div>";
                }
                echo "</div>";
                return true;
            }
        }

        function reloadPlaylists(): bool{
            $key = "AIzaSyCV1o4wQaHEwxxdq9XncfAdj590MrIyMnQ";
            $base_url = "https://www.googleapis.com/youtube/v3/";
            $channelId = "UCSrwdI8538RKP14Aq49Fn4Q";
            $maxResult = 50; 
            
            $API_URL = $base_url."playlists?order=date&part=snippet&channelId=".$channelId."&maxResults=".$maxResult."&key=".$key;
            $playlists = json_decode(file_get_contents($API_URL));
            return true;              
        }
                
        function checkUpdateAvailable(){         
            try{
                require_once("dbConnect.php");
                $db = new DbConnect();
                $conn = $db->connect(); 

                $sql = "SELECT id, playlist_id, etag from playlists";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $dbPlaylists = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $db = null;
            
                $arrAux = [];
                for($i = 0; $i < sizeof($dbPlaylists); $i++){
                    $dbObj = $dbPlaylists[$i];
                    array_push($arrAux, $dbObj['playlist_id']);
                }

                $key = "AIzaSyCV1o4wQaHEwxxdq9XncfAdj590MrIyMnQ";
                $base_url = "https://www.googleapis.com/youtube/v3/";
                $channelId = "UCSrwdI8538RKP14Aq49Fn4Q";
                $maxResult = 50;
                $API_URL = $base_url."playlists?order=date&part=snippet&channelId=".$channelId."&maxResults=".$maxResult."&key=".$key;
                $playlists = json_decode(file_get_contents($API_URL));
                $auxArrIdx = -1;

                foreach($playlists->items as $playlist){
                    $auxArrIdx = array_search($playlist->id, $arrAux);
                    if($auxArrIdx != false){
                        $dbObj = $dbPlaylists[$auxArrIdx];
                        if($dbObj['playlist_id'] == $playlist->id){
                            //atualizar etag da playlist e videos
                            if($dbObj['etag'] != $playlist->etag){
                                echo "<script>console.log('Atualização disponível para playlist: '+".json_encode($playlist->snippet->title).")</script>";
                                if($this->updatePlaylist($playlist, $dbObj['id']) == false) echo "<script>console.log('Não foi possível atualizar a playlist: '+".json_encode($playlist->snippet->title).")</script>";
                            }
                        }
                    } else // inserir playlist nova
                        if($this->registerPlaylist($playlist, $dbObj['id']) == false) throw new Exception('Não foi possível registrar a playlist: '.$playlist->title);
                }
            }
            catch(PDOException $e){
                echo "<script>console.log('Erro: ".$e->getMessage()."')</script>";
                $db = null;
            }
        }

        function updatePlaylist($playlist, $playID): bool{
            $result = false;
            try{
                require_once("dbConnect.php");
                $db = new DbConnect();
                $conn = $db->connect();

                $publish_date = str_replace("T"," ",$playlist->snippet->publishedAt);
                $publish_date = str_replace("Z"," ",$publish_date);
                if(isset($playlist->snippet->thumbnails->standard->url)){
                    $thumbnail = $playlist->snippet->thumbnails->standard->url;
                } else if(isset($playlist->snippet->thumbnails->high->url)){
                    $thumbnail = $playlist->snippet->thumbnails->high->url;
                } else if(isset($playlist->snippet->thumbnails->maxres->url)){
                    $thumbnail = $playlist->snippet->thumbnails->maxres->url;
                }
                $sql = "call update_Playlist(:ppid, :pstrpid, :ptitle, :pthumbnail, :ppublished_at, :petag)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":ppid",$playID); // parm playlist id
                $stmt->bindParam(":pstrpid",$playlist->id);
                $stmt->bindParam(":ptitle",$playlist->snippet->title);
                $stmt->bindParam(":pthumbnail",$thumbnail);
                $stmt->bindParam(":ppublished_at",$publish_date);
                $stmt->bindParam(":petag",$playlist->etag);
                if($stmt->execute() == true){
                    $db = null; // Fecha a conexão anterior

                    require_once("dbConnect.php");
                    $db = new DbConnect();
                    $conn = $db->connect();

                    $sql = "call search_idVideos_by_playlist(:pstridplaylist)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":pstridplaylist",$playlist->id);
                    $stmt->execute();
                    $dbVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $db = null;

                    if(count($dbVideos) == 0 || $dbVideos == null){
                        $msg = "Não há vídeo salvos nesta playlist. Para carregar os vídeos desta playlist volte à página inicial e clique no link \"Ver vídeos\".";
                        echo "<script>console.log('".$msg."');</script>";
                    } else{
                        require_once('videosUtils.class.php');
                        $videoUtils = new VideosUtils();

                        $API_URL = $this->_setUrl(10,'',$playlist->id);
                        $videos = json_decode(file_get_contents($API_URL));
                        $totalResult = isset($videos->pageInfo->totalResults) ? $videos->pageInfo->totalResults : 0;
                        if($totalResult == 0) echo "<script>console.log('Não há vídeos para esta playlist: '".json_encode($playlist->snippet->title).");</script>";
                        $rest = $totalResult % 10;
                        $t = round($totalResult / 10);

                        for($i = 1; $i <= $t; $i++){
                            foreach($videos->items as $video){
                                $auxArrIdx = array_search($video->id, $dbVideos);
                                if($auxArrIdx == false){ // Se o vídeo corrente não estiver entre os registrados no banco, executa a inserção
                                    $videoUtils->registerVideo($video, $playID);
                                } 
                            }
                            if($i != $t){
                                $maxResult = ($i != ($t-1)) ? 10 : 10 + $rest;
                                $pageToken = isset($videos->nextPageToken) ? $videos->nextPageToken : '';
                                $videos = null;
                                $API_URL = $this->_setUrl($maxResult, $pageToken, $playlist->id);
                                $videos = json_decode(file_get_contents($API_URL));
                            }
                        }
                        $result = true;
                    }
                }
                return $result;
            }catch(PDOException $e){
                return $result;
            }
        }

        function _setUrl($maxResult, $pageToken, $playlistId){
            $key = "AIzaSyCV1o4wQaHEwxxdq9XncfAdj590MrIyMnQ";
            $base_url = "https://www.googleapis.com/youtube/v3/";
            $channelId = "UCSrwdI8538RKP14Aq49Fn4Q";
            $API_URL = $base_url."playlistItems?order=date&part=snippet&channelId=".$channelId."&playlistId=".$playlistId."&maxResults=".$maxResult."&key=".$key;
            if($pageToken != ''){
                $API_URL .= "&pageToken=".$pageToken;
            }
            return $API_URL;
        }
    }



    /*echo "<iframe id=".$playlist['id']."
    src='https://www.youtube.com/embed/?listType=playlist&list=".$playlist['playlist_id']."' frameborder='0' allow='accelerometer; 
    autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";*/






?>
