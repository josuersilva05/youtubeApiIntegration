<?php
    class DbConnect{
        private $host = "localhost";
        private $dbName = "youtubePlaylists";
        private $user = "root";
        private $pass = "1234567";
        
        public function connect(){
            try{
                $conn = new PDO('mysql:host='.$this->host.";dbname=".$this->dbName, $this->user, $this->pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $conn;
                
            } catch( PDOException $e){
                echo "<script>alert('Database error: " . $e->getMessage(). ");</script>";
                return null;
            }   
        }
    }
?>