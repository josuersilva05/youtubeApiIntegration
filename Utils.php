<?php            

            $teste = "abc";

            echo $teste;

            $key = "AIzaSyCV1o4wQaHEwxxdq9XncfAdj590MrIyMnQ";
            $base_url = "https://www.googleapis.com/youtube/v3/";
            $channelId = "UCSrwdI8538RKP14Aq49Fn4Q";
            $maxResult = 50; 
            
            $API_URL = $base_url."playlists?order=date&part=snippet&channelId=".$channelId."&maxResults=".$maxResult."&key=".$key;
            $playlists = json_decode($API_URL);    



            include "dbConnect.php";
            $db = new DbConnect();
            $conn = $db->connect();
            exit;     

            $key = "AIzaSyCV1o4wQaHEwxxdq9XncfAdj590MrIyMnQ";
            $base_url = "https://www.googleapis.com/youtube/v3/";
            $channelId = "UCSrwdI8538RKP14Aq49Fn4Q";
            $maxResult = 20; 
            //This first url brings up both videos and playlists
            //$API_URL = $base_url."search?order=date&part=snippet&channelId=".$channelId."&maxResults=".$maxResult."&key=".$key;
            $API_URL = $base_url."playlists?order=date&part=snippet&channelId=".$channelId."&maxResults=".$maxResult."&key=".$key;
            // Passing a parameter through url. If a parameter 'vtype' is set, then its value:
            //$video_type = !isset($_GET['vtype']) ? 1: 2;

            $playlists = json_decode(file_get_contents($API_URL));

            function getPlaylists(){
                foreach($playlists->items as $playlist){
                    $id = "";
                    if (!isset($playlist->id->playlistId)) $id = $playlist->id->videoId;
                    else $id = $playlist->id->playlistId;
                    $publish_date = str_replace("T"," ",$playlist->snippet->publishedAt);
                    $publish_date = str_replace("Z"," ",$playlist->snippet->publishedAt);

                    $sql = "INSERT INTO videos(id, video_type, video_id, title, thumbnail_url, published_at) values(NULL, 1, :vid, :title, :turl, :pdate)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":vid", $id);
                    $stmt->bindParam(":title", $playlist->snippet->title);
                    $stmt->bindParam(":turl", $playlist->snippet->thumbnails->high->url);
                    $stmt->bindParam(":pdate", $publish_date);
                    $stmt->execute();
                }
            }

            function getVideos(){
                foreach($videos->items as $video){
                    $id = "";
                    if (!isset($video->id->playlistId)) $id = $playlist->id->videoId;
                    else $id = $video->id->playlistId;
                    $publish_date = str_replace("T"," ",$video->snippet->publishedAt);
                    $publish_date = str_replace("Z"," ",$video->snippet->publishedAt);

                    $sql = "INSERT INTO videos(id, video_type, video_id, title, thumbnail_url, published_at) values(NULL, 1, :vid, :title, :turl, :pdate)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":vid", $id);
                    $stmt->bindParam(":title", $video->snippet->title);
                    $stmt->bindParam(":turl", $video->snippet->thumbnails->high->url);
                    $stmt->bindParam(":pdate", $publish_date);
                    $stmt->execute();
                }
            }

            echo "<pre>";
            //print_r($videos);
            echo "</pre>";
            //*/
            //echo $API_URL; 
?>