<?php
// Accessing the Connection and Responses classes
require_once "Connection/Connection.php";
require_once "Responses.class.php";
// Products inherits from Connection
class Products extends Connection {
    // Responses
    // Table
    private $table = "products";
    // Assign the variables
    private $product_id = "";
    private $product_uid = "";
    private $product_name = "";
    private $product_class = "";
    private $product_price = "";
    private $product_price_discount = "";
    private $product_unique_piece = "";
    private $product_description = "";
    private $product_description_es = "";
    private $product_weight = "";
    private $product_stock = "";
    private $product_sizes = "";
    private $product_image = "";
    private $product_images_gallery = "";
    private $product_images_thumbnails = "";
    private $product_date = "";
    // Getting products method
    public function getProducts($page = 1) {
        $Responses = new Responses();
        $start = 0;
        $qty = 15;
        if ($page > 1) $start = $qty * ($page - 1);
        $query =
            "SELECT 
                `product_id`,
                `product_uid`,
                `product_name`,
                `product_class`,
                `product_price`,
                `product_price_discount`,
                `product_unique_piece`,
                `product_stock`,
                `product_image`
            FROM ".$this->table." ORDER BY `product_id` ASC limit $start, $qty";
        try {
            $data = parent::getData($query);
            if (!isset($data)) return $this->Responses->error_500();
            return $data;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
    // Getting products by id method
    public function getProduct($product_id) {
        $Responses = new Responses();
        $query =
            "SELECT 
                `product_id`,
                `product_uid`,
                `product_name`,
                `product_class`,
                `product_price`,
                `product_price_discount`,
                `product_description`,
                `product_description_es`,
                `product_stock`,
                `product_sizes`,
                `product_images_gallery`,
                `product_images_thumbnails`,
                `product_image`
            FROM ".$this->table." WHERE `product_id` = '$product_id'";
        try {
            $data = parent::getData($query);
            if (!isset($data)) return $this->Responses->error_500();
            return $data;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
}