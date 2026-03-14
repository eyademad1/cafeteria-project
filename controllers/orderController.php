<?php
require_once "./models/product.php";
class orderController{
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

   public function index(){
    $productModel=new product($this->conn);
    return $productModel->getAllProducts();
   }



}