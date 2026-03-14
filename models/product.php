<?php
    class product{
        private $conn;
        public function __construct($conn){
            $this->conn=$conn;
        }
        public function getAllProducts(){
            $query="select * from products";
            $stmt=$this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    }
 