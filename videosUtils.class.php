<?php
    class VideosUtils{

        function searchForVideosByPlaylist($itemId,$playlistId){
            require_once('playlistsUtils.class.php');
            $playUtils = new PlaylistsUtils();

            $API_URL = $playUtils->_setUrl(10,'',$playlistId);
            $videos = json_decode(file_get_contents($API_URL));
            $totalResult = isset($videos->pageInfo->totalResults) ? $videos->pageInfo->totalResults : 0;
            if($totalResult == 0) return false;
            $rest = $totalResult % 10;
            $t = round($totalResult / 10);

            for($i = 1; $i <= $t; $i++){
                foreach($videos->items as $video) $this->registerVideo($video, $itemId);
                if($i != $t){
                    $maxResult = ($i != ($t-1)) ? 10 : 10 + $rest;
                    $pageToken = isset($videos->nextPageToken) ? $videos->nextPageToken : '';
                    $videos = null;
                    $API_URL = $playUtils->_setUrl($maxResult, $pageToken, $playlistId);
                    $videos = json_decode(file_get_contents($API_URL));
                }
            }
            $db = null;
            $this->loadVideos($itemId);
        }

        function registerVideo($video, $itemId): bool{
            try{
                require_once("dbConnect.php");
                $db = new DbConnect();
                $conn = $db->connect();
                if ($conn == null) throw new Exception('Conexão não pôde ser estabelecida.');
                
                $publish_date = str_replace("T"," ",$video->snippet->publishedAt);
                $publish_date = str_replace("Z"," ",$publish_date);
                if(isset($video->snippet->thumbnails->standard->url)){
                    $thumbnail = $video->snippet->thumbnails->standard->url;
                } else if(isset($video->snippet->thumbnails->high->url)){
                    $thumbnail = $video->snippet->thumbnails->high->url;
                } else if(isset($video->snippet->thumbnails->maxres->url)){
                    $thumbnail = $video->snippet->thumbnails->maxres->url;
                }
                $sql = "call insert_video(:pvid, :pvtitle, :pvthumbUrl, :pvpublish, :pvetag, :pvplaylistId)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":pvid",$video->id);
                $stmt->bindParam("pvtitle",$video->snippet->title);
                $stmt->bindParam("pvthumbUrl",$thumbnail);
                $stmt->bindParam("pvpublish",$publish_date);
                $stmt->bindParam("pvetag",$video->etag);
                $stmt->bindParam("pvplaylistId",$itemId);
                if($stmt->execute()){
                    return true;
                }
                return false;
            } catch(PDOException $e){
                echo "<script>console.log('Error while searching for videos: '+".json_enconde($e->getMessage()).")</script>";
                return false;
            }
        }

        
        function loadVideos($intPid): bool{
            try{
                require_once("dbConnect.php");
                $db = new DbConnect();
                $conn = $db->connect();

                $sql = "SELECT * from videos where idPlaylist=$intPid";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $db = null;

                if(count($videos) == 0 || $videos == null){
                    return false;
                } else{
                    echo "<div class='row'>";
                    foreach($videos as $video){
                        echo "<div class='col-md-3'>";
                        echo "<div class='thumb-container' id=".$video['id'].">
                        <img class='thumb' src=".$video['thumbnail_url'].">
                        </div>";
                        echo "<div class='title-container'>";
                        echo "<b>".$video['title']."</b>";
                        echo "</div>";
                        echo "</div>";
                    }
                    echo "</div>";
                    return true;
                }
            }
            catch(PDOException $e){
                echo "Error while searching videos: ".$e->getMessage();
                return false;
            }
        }

    }