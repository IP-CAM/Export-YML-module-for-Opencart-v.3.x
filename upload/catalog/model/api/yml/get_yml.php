<?php
class ModelApiYmlGetYml extends Model {

    /**
     * Getting categories
     * @return bool|object
     */
    public function getCategories(){
        $result = $this->db->query("SELECT `category_id`, `name` FROM `" . DB_PREFIX . "category_description` WHERE 1 ORDER BY `category_id`");
        if ($result->num_rows > 0) {
            return $result;
        }
        return false;
    }

    /**
     * Getting products
     * @return bool || result objectpyftn&
     */
    public function getProducts(){
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product` WHERE `status` = 1");
        if ($result->num_rows > 0) {
            return $result;
        }
        return false;
    }

    /**
     * Getting product name by ID
     * @param $product_id
     * @return bool|string
     */
    public function getProductName($product_id){
        $result = $this->db->query("SELECT `name` FROM `" . DB_PREFIX . "product_description` WHERE `product_id` = '$product_id'");
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                return $row['name'];
            }
        }
        return false;
    }

    /**
     * Getting attributes of product
     * @param $product_id
     * @return bool|object
     */
    public function getAttributes($product_id){
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_attribute` WHERE `product_id` = '$product_id'");
        if ($result->num_rows > 0) {
            return $result;
        }
        return false;
    }

    /**
     * Getting name of the attribute by id
     * @param $attributeId
     * @return boolean|String
     */
    public function getNameAttributeById($attributeId){
        $result = $this->db->query("SELECT `name` FROM `" . DB_PREFIX . "attribute_description` WHERE `attribute_id` = '$attributeId'");
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                return $row['name'];
            }
        }
        return false;
    }

    /**
     * Getting attribute sort order by id
     * @param $attributeId
     * @return int
     */
    public function getAttributeSortOrder($attributeId){
        $result = $this->db->query("SELECT `sort_order` FROM `" . DB_PREFIX . "attribute` WHERE `attribute_id` = '$attributeId'");
        $sortOrder = 0;
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                $sortOrder = $row['sort_order'];
            }
        }
        if ($sortOrder == 0) $sortOrder = 50;
        return $sortOrder;
    }

    /**
     * Getting special price by product ID
     * @param $productId
     * @param $priceId
     * @return bool|Int
     */
    public function getSpecialPrice($productId, $priceId){
        $result = $this->db->query("SELECT `price` FROM `" . DB_PREFIX . "product_special` WHERE `product_id` = '$productId' AND `customer_group_id`='$priceId'");
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                return $row['price'];
            }
        }
        return false;
    }

    public function getDiscountValue($productId, $customerGroupId){
        $result = $this->db->query("SELECT `price` FROM `" . DB_PREFIX . "product_discount` WHERE `product_id` = '$productId' AND `customer_group_id`='$customerGroupId'");
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                return $row['price'];
            }
        }
        return false;
    }

    /**
     * Getting additional images by product ID
     * @param $product_id
     * @return bool|array
     */
    public function getImages($product_id){
        $result = $this->db->query("SELECT `image` FROM `" . DB_PREFIX . "product_image` WHERE `product_id` = '$product_id'");
        if ($result->num_rows > 0) {
            return $result;
        }
        return false;
    }

    /**
     * Getting vendor name by $manufacturerId
     * @param $manufacturerId
     * @return bool|string
     */
    public function getVendorName($manufacturerId){
        $result = $this->db->query("SELECT `name` FROM `" . DB_PREFIX . "manufacturer` WHERE `manufacturer_id` = '$manufacturerId'");
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                return preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $row['name']);
            }
        }
        return false;
    }

    /**
     * Getting product description by product ID
     * @param $product_id
     * @return bool|array
     */
    public function getProductDescription($product_id){
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_description` WHERE `product_id` = '$product_id'");
        if ($result->num_rows > 0) {
            return $result;
        }
        return false;
    }

    /**
     * Getting product PROM ID by model
     * @param $model
     * @return bool|string
     */
    public function getProductPromId($model){
        $result = $this->db->query("SELECT `id_prom` FROM `" . DB_PREFIX . "prom_product` WHERE `model` = '$model'");
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                return $row['id_prom'];
            }
        }
        return false;
    }

    /**
     * Getting product CATEGORY PROM ID by model
     * @param $model
     * @return bool|string
     */
    public function getProductPromCategoryId($model){
        $result = $this->db->query("SELECT `id_group_prom` FROM `" . DB_PREFIX . "prom_product` WHERE `model` = '$model'");
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                return $row['id_group_prom'];
            }
        }
        return false;
    }

    /**
     * Getting product 1C CATEGORY ID by model
     * @param $model
     * @return bool|Int
     */
    public function getCategoryId($model){
        $result = $this->db->query("SELECT `category_id` FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = '$model'");
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                return $row['category_id'];
            }
        }
        return false;
    }

    /**
     * Getting category data by category name
     * @param $name
     * @return bool|array
     */
    public function getCategoryProm($name){
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "prom_category` WHERE `name` LIKE '$name'");
        if ($result->num_rows > 0) {
            return $result;
        }
        return false;
    }

    /**
     * Getting category data by category id from 1C
     * @param $category_id
     * @return bool|array
     */
    public function getCategoryPromByRealId($category_id){
        $result = $this->db->query("SELECT `id_prom` FROM `" . DB_PREFIX . "prom_category` WHERE `category_id` = '$category_id'");
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                return $row['id_prom'];
            }
        }
        return false;
    }

    /**
     * Getting wholesale price by product ID (customer price ID = 16)
     * @param $productId
     * @param $priceId
     * @return bool|Int
     */
    public function getWholesalePrice($productId, $priceId){
        $result = $this->db->query("SELECT `price` FROM `" . DB_PREFIX . "product_discount` WHERE `product_id` = '$productId' AND `customer_group_id`='$priceId'");
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                return $row['price'];
            }
        }
        return false;
    }

}