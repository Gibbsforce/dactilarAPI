<?php
// Accessing the Connection and Responses classes
require_once "Connection/Connection.php";
require_once "Responses.class.php";
// Cart inherits from Connection
class Cart extends Connection {
    private $token = "";
    private $key = "";
    // Getting the cart from user
    public function getCartFromUser($token, $uname) {
        $Responses = new Responses();
        $this->token = $token;
        $arr_token = $this->searchToken();
        if (!$arr_token) return $Responses->error_401("Unauthorized or your token has been deprecated");
        $username = $arr_token[0]["username"];
        if ($username !== $uname) return $Responses->error_401();
        $result = $this->getCartByUser($username);
        if (!$result) return $Responses->error_404();
        return $result;
    }
    // POST items to the cart
    public function addToCart($json) {
        $Responses = new Responses();
        $data = json_decode($json, true);
        if (!isset($data["token"])) return $Responses->error_401();
        $this->token = $data["token"];
        $arr_token = $this->searchToken();
        if (!$arr_token) return $Responses->error_401("Unauthorized or your token has been deprecated");
        $username = $arr_token[0]["username"];
        if (!$data["cart"]) return $Responses->error_400();
        $cart = $data["cart"];

        $cart_result = $this->getCartByUser($username);
        if (!$cart_result) return $Responses->error_404();
        $cart_static = $cart_result["cart_result"];
    
        if (!$cart_static) $cart_static = array();
        $cart = array_merge($cart_static, $cart);

        for ($i = 0; $i < count($cart); $i++) {
            if (!isset($cart[$i]["product_uid"])) return $Responses->error_401("product_uid is empty");
            if (!isset($cart[$i]["product_name"])) return $Responses->error_401("product_name is empty");
            if (!isset($cart[$i]["product_image"])) return $Responses->error_401("product_image is empty");
            if (!isset($cart[$i]["product_stock"])) return $Responses->error_400("product_stock is empty");
            if (!isset($cart[$i]["product_quantity"])) return $Responses->error_401("product_quantity is empty");
            if (!isset($cart[$i]["product_size"])) return $Responses->error_401("product_size is empty");
            if (!isset($cart[$i]["product_price"])) return $Responses->error_401("product_price is empty");
            if (!isset($cart[$i]["product_price_discount"])) return $Responses->error_401("product_price_discount is empty");
            if (!isset($cart[$i]["product_final_price"])) return $Responses->error_401("product_final_price is empty");   
        }
        foreach ($cart as $row) {
            $result[$row["product_uid"]] = [
                "product_uid" => $row["product_uid"],
                "product_quantity" => ($result[$row["product_uid"]]["product_quantity"] ?? 0) + $row["product_quantity"]
            ];
        }
        $total_quantity = array_values($result);
        $arr_product_uid = array();
        $arr_product_sizes = array();
        $products = $this->allProducts();
        for ($i = 0; $i < count($products); $i++) {
            for ($j = 0; $j < count($total_quantity); $j++) {
                if ($total_quantity[$j]["product_uid"] === $products[$i]["product_uid"]) {
                    if ($total_quantity[$j]["product_quantity"] > $products[$i]["product_stock"]) {
                        return $Responses->error_401("What are you doing, dude?");
                    }
                }
            }
            array_push($arr_product_uid, $products[$i]["product_uid"]);
            if (!$products[$i]["product_sizes"]) $products[$i]["product_sizes"] = "noSize";
            array_push($arr_product_sizes, explode(",", $products[$i]["product_sizes"]));
        }
        for ($i = 0; $i < count($cart); $i++) {
            if (!in_array($cart[$i]["product_uid"], $arr_product_uid)) return $Responses->error_401("What are you doing, dude?");
            for ($j = 0; $j < count($products); $j++) {
                if ($cart[$i]["product_uid"] === $products[$j]["product_uid"]) {
                    if (!in_array($cart[$i]["product_size"], $arr_product_sizes[$j])) return $Responses->error_401("What are you doing, dude?");
                    $arr_cart[$i] = array(
                            "product_uid" => $products[$j]["product_uid"],
                            "product_name" => $products[$j]["product_name"],
                            "product_image" => $products[$j]["product_image"],
                            "product_stock" => intval($products[$j]["product_stock"]),
                            "product_quantity" => $cart[$i]["product_quantity"],
                            "product_size" => $cart[$i]["product_size"],
                            "product_price" => floatval($products[$j]["product_price"]),
                            "product_price_discount" => floatval($products[$j]["product_price_discount"]),
                            "product_final_price" => $products[$j]["product_price_discount"] > 0 ? $cart[$i]["product_quantity"] * $products[$j]["product_price_discount"] : $cart[$i]["product_quantity"] * $products[$j]["product_price"]
                        );
                }
            }
        }
        $cart = json_encode($arr_cart);
        $query = "UPDATE `users` SET `cart` = '$cart' WHERE username = '$username'";
        $query_cart_added = "SELECT `cart` FROM `users` WHERE username = '$username'";
        try {
            $data = parent::nonQuery($query);
            $cart_added = parent::getData($query_cart_added);
            $result = array(
                "message" => "OK",
                "result" => $data,
                "cart_result" => json_decode($cart_added[0]["cart"], true)
            );
            return $result;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
    // DELETE items from cart
    public function deleteItemFromCart($json) {
        $Responses = new Responses();
        $data = json_decode($json, true);
        if (!isset($data["token"])) return $Responses->error_400();
        $this->token = $data["token"];
        if (!isset($data["key"])) return $Responses->error_400();
        $this->key = intval($data["key"]);
        $arr_token = $this->searchToken();
        if (!$arr_token) return $Responses->error_401("Unauthorized or your token has been deprecated");
        $username = $arr_token[0]["username"];

        $cart_result = $this->getCartByUser($username);
        if (!$cart_result) return $Responses->error_404();
        $cart_static = $cart_result["cart_result"];
        array_splice($cart_static, $this->key, 1);

        $cart = json_encode($cart_static);
        $query = "UPDATE `users` SET `cart` = '$cart' WHERE username = '$username'";
        $query_cart_added = "SELECT `cart` FROM `users` WHERE username = '$username'";
        try {
            $data = parent::nonQuery($query);
            $cart_added = parent::getData($query_cart_added);
            $result = array(
                "message" => "OK",
                "result" => $data,
                "cart_result" => json_decode($cart_added[0]["cart"], true)
            );
            return $result;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }

    }
    // Getting the cart by user
    private function getCartByUser($uname) {
        $query = "SELECT `cart` FROM `users` WHERE username = '$uname'";
        try {
            $data = parent::getData($query);
            if (!isset($data)) return $this->Responses->error_500();
            $result = array(
                "message" => "OK",
                "cart_result" => json_decode($data[0]["cart"], true)
            );
            return $result;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
    // Getting the products
    private function allProducts() {
        $query = "SELECT * FROM `products`";
        try {
            $data = parent::getData($query);
            if (!isset($data)) return $this->Responses->error_500();
            return $data;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
    // Looking for the token method
    private function searchToken() {
        $query = "SELECT `id-token`, `username`, `state`, `status` FROM `users-token` WHERE `token` = '".$this->token."' AND `state` = 1 AND (`status` = 'user' OR `status` = 'admin')";
        try {
            $result = parent::getData($query);
            if ($result) return $result;
            return false;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
}