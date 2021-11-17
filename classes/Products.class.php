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
    // External variables
    private $token = "";
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
    // POST product
    public function post($json) {
        // Responses
        $Responses = new Responses();
        // Getting the data from the client
        $data = json_decode($json, true);
        // Validating token
        if (!isset($data["token"])) return $Responses->error_401();
        $this->token = $data["token"];
        $arr_token = $this->searchToken();
        if (!$arr_token) return $Responses->error_401();
        // Mandatory fields
        if (
            !isset($data["product_name"]) ||
            !isset($data["product_class"]) ||
            !isset($data["product_price"]) ||
            !isset($data["product_price_discount"]) ||
            !isset($data["product_unique_piece"]) ||
            !isset($data["product_description"]) ||
            !isset($data["product_description_es"]) ||
            !isset($data["product_weight"]) ||
            !isset($data["product_stock"]) ||
            !isset($data["product_sizes"]) ||
            !isset($data["product_image"])
        ) return $Responses->error_400();
        // Storing the data into the variables
        $this->product_name = $data["product_name"];
        $this->product_class = $data["product_class"];
        $this->product_price = $data["product_price"];
        $this->product_price_discount = $data["product_price_discount"];
        $this->product_unique_piece = $data["product_unique_piece"];
        $this->product_description = $data["product_description"];
        $this->product_description_es = $data["product_description_es"];
        $this->product_weight = $data["product_weight"];
        $this->product_stock = $data["product_stock"];
        $this->product_sizes = $data["product_sizes"];
        $product_image = $this->productImage($data["image"]);
        $product_image_thumb = $this->productImageThumbnails($product_image);
        $this->product_image = $product_image_thumb[1];
        // No mandatory
        // Images gallery
        if (isset($data["product_images_gallery"])) {
            $product_images_gallery = $this->productImagesGallery($data["product_images_gallery"]);
            $this->product_images_gallery = $product_images_gallery[1];
            // Images thumbnails
            $thumbnails = $this->productImagesThumbnails($product_images_gallery);
            $this->product_images_thumbnails = $thumbnails[1];
        }
        // Saving the product
        $product = $this->createProduct();
        if (!$product) return $Responses->error_500();
        $response = $Responses->response;
        $response["result"] = array(
            "product_id" => $product
        );
        return $response;
    }
    // Creating products method
    private function createProduct() {
        $this->product_date = date("Y-m-d H:i");
        $this->product_uid = uniqid();
        $query = "INSERT INTO ".$this->table." (
            `product_uid`,
            `product_name`,
            `product_class`,
            `product_price`,
            `product_price_discount`,
            `product_unique_piece`,
            `product_description`,
            `product_description_es`,
            `product_weight`,
            `product_stock`,
            `product_sizes`,
            `product_image`,
            `product_images_gallery`,
            `product_images_thumbnails`,
            `product_date`)VALUES(
                '".$this->product_uid."',
                '".$this->product_name."',
                '".$this->product_class."',
                '".$this->product_price."',
                '".$this->product_price_discount."',
                '".$this->product_unique_piece."',
                '".$this->product_description."',
                '".$this->product_description_es."',
                '".$this->product_weight."',
                '".$this->product_stock."',
                '".$this->product_sizes."',
                '".$this->product_image."',
                '".$this->product_images_gallery."',
                '".$this->product_images_thumbnails."'
                '".$this->product_date."',
            )";
        try {
            $product = parent::nonQuerId($query);
            if ($product) return $product;
            return false;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
    // Method that proccess the data products
    }
    private function productImage($product_image) {
        $dir = dirname(__DIR__)."/public/products/image/";
        $array_image = explode(";base64,", $product_image);
        $ext = explode("/", mime_content_type($product_image))[1];
        $image_base64 = base64_decode($array_image[1]);
        $file = $dir.uniqid().".".$ext;
        file_put_contents($file, $image_base64);
        $domain = $this->domain;
        $local_file = str_replace(dirname(__DIR__), $domain, $file);
        $arr_file = array($file, $local_file);
        return $arr_file;
    }
    private function productImagesGallery($product_images_gallery) {
        $dir = dirname(__DIR__)."/public/products/images/";
        foreach ($product_images_gallery as $key => $image) {
            $images[] = explode(" ,", $image);
        }
        for ($i = 0; $i < count($product_images_gallery); $i++) {
            $images_gallery[] = explode(";base64,", $images[$i][0]);
            $ext[] = explode("/", mime_content_type($images[$i][0]))[1];
            $image_base64[] = base64_decode($images_gallery[$i][1]);
            $files[] = $dir.uniqid().".".$ext[$i];
            file_put_contents($files[$i], $image_base64[$i]);
        }
        $files_name = implode(",", $files);
        $domain = $this->domain;
        $local_files = str_replace(dirname(__DIR__), $domain, $files_name);
        $arr_files = array($files_name, $local_files);
        return $arr_files;
    }
    private function productImageThumbnails($file) {
        $src = $file[0];
        $resize = 0.5;
        $img = explode(".", $src);
        $ext = $img[count($img) - 1];
        if ($ext == "jpeg") {
            $dest = dirname(__DIR__)."/public/products/image/thumbnails/".uniqid().".".$ext;
            $size = getimagesize($src);
            $width = $size[0];
            $height = $size[1];
            $new_width = ceil($width * $resize);
            $new_height = ceil($height * $resize);
            $origin = imagecreatefromjpeg($src);
            $resized = imagecreatetruecolor($new_width, $new_height);
            imagecopyresized($resized, $origin, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($resized, $dest);
            imagedestroy($origin);
            imagedestroy($resized);
        } elseif ($ext == "png") {
            $dest = dirname(__DIR__)."/public/products/image/thumbnails/".uniqid().".".$ext;
            $size = getimagesize($src);
            $width = $size[0];
            $height = $size[1];
            $new_width = ceil($width * $resize);
            $new_height = ceil($height * $resize);
            $origin = imagecreatefrompng($src);
            $resized = imagecreatetruecolor($new_width, $new_height);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresized($resized, $origin, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagepng($resized, $dest);
            imagedestroy($origin);
            imagedestroy($resized);
        } else {
            return null;
        }
        $api_dest = implode(",", $dest);
        $domain = $this->domain;
        $local_dest = str_replace(dirname(__DIR__), $domain, $api_dest);
        $arr_dest = array($api_dest, $local_dest);
        return $arr_dest;
    }
    private function productImagesThumbnails($arr_files) {
        $str = $arr_files[0];
        $src = explode(",", $str);
        
        $resize = 0.5;
        
        for ($i = 0; $i < count($src); $i++) {
            $img[] = explode(".", $src[$i]);
            $ext[] = $img[$i][1];
            if ($ext[$i] == "jpeg") {
                $dest[] = dirname(__DIR__)."/public/products/images/thumbnails/".uniqid().".".$ext[$i];
                $size[] = getimagesize($src[$i]);
                $width[] = $size[$i][0];
                $height[] = $size[$i][1];
                $r_width[] = ceil($width[$i] * $resize);
                $r_height[] = ceil($height[$i] * $resize);
                $origin[] = imagecreatefromjpeg($src[$i]);
                $resized[] = imagecreatetruecolor($r_width[$i], $r_height[$i]);
                imagecopyresampled(
                    $resized[$i], $origin[$i],
                    0, 0, 0, 0,
                    $r_width[$i], $r_height[$i],
                    $width[$i], $height[$i]
                );
                imagejpeg($resized[$i], $dest[$i]);
                imagedestroy($origin[$i]);
                imagedestroy($resized[$i]);
            } else if ($ext[$i] == "png") {
                $dest[] = dirname(__DIR__)."/public/products/images/thumbnails/".uniqid().".".$ext[$i];
                $size[] = getimagesize($src[$i]);
                $width[] = $size[$i][0];
                $height[] = $size[$i][1];
                $r_width[] = ceil($width[$i] * $resize);
                $r_height[] = ceil($height[$i] * $resize);
                $origin[] = imagecreatefrompng($src[$i]);
                $resized[] = imagecreatetruecolor($r_width[$i], $r_height[$i]);
                imagealphablending($resized[$i], false);
                imagesavealpha($resized[$i], true);
                imagecopyresampled(
                    $resized[$i], $origin[$i],
                    0, 0, 0, 0,
                    $r_width[$i], $r_height[$i],
                    $width[$i], $height[$i]
                );
                imagepng($resized[$i], $dest[$i]);
                imagedestroy($origin[$i]);
                imagedestroy($resized[$i]);
            } else {
                return null;
            }
        }
        $api_dest = implode(",", $dest);
        $domain = $this->domain;
        $local_dest = str_replace(dirname(__DIR__), $domain, $api_dest);
        $arr_dest = array($api_dest, $local_dest);
        return $arr_dest;
    }
    // Domain
    private domain() {
        $domain = "http".((array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] && strtolower($_SERVER["HTTPS"]) !== "off") ? "s" : null)."://".$_SERVER["HTTP_HOST"];
        print_r($domain);
        return $domain;
    }
    // Looking for the token method
    private function searchToken() {
        $query = "SELECT `id-token`, `unique-id`, `state`, `status` FROM `users-token` WHERE `token` = '".$this->token."' AND `state` = 1 AND `status` = `admin`";
        try {
            $result = parent::getData($query);
            if ($result) return $result;
            return false;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
    // Updating token method
    private function updateToken($id_token) {
        $date = date("Y-m-d H:i");
        $query = "UPDATE `users-token` SET `date` = '".$date."' WHERE `id-token` = '".$id_token."'";
        try {
            $updated = parent::nonQuery($query);
            if ($updated > 0) return $updated;
            return false;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
}