<?php
class ModelExtensionModuleExportYml extends Model {

    public function installPromCategoryTable() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "prom_category` (
            `id` INT(11) NOT NULL AUTO_INCREMENT, 
            PRIMARY KEY(`id`), `name` TEXT, `id_prom` TEXT, `parent_id_prom` TEXT, `category_id` INT(11), `parent_id` INT(11)) CHARACTER SET utf8 COLLATE utf8_general_ci");
    }

    public function uninstallPromCategoryTable() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "prom_category`");
    }

    public function installPromProductTable() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "prom_product` (
            `id` INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`), `id_prom` TEXT, `model` INT(11), `id_group_prom` TEXT) 
            CHARACTER SET utf8 COLLATE utf8_general_ci");
    }

    public function uninstallPromProductTable() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "prom_product`");
    }

    /**
     * Checking is product exist by model
     * @param $model
     * @return bool
     */
    public function isProductExist($model){
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "prom_product` WHERE `model` = '$model'");
        return $result->num_rows > 0 ? true : false;
    }

    /**
     * Adding a product
     * @param $data
     */
    public function insertProduct($data){
        $id_prom = $data['id_prom'];
        $model = $data['model'];
        $id_group_prom = $data['id_group_prom'];
        $this->db->query("INSERT INTO `" . DB_PREFIX . "prom_product` (id_prom, model, id_group_prom) 
        VALUES('$id_prom', '$model', '$id_group_prom')");
    }

    /**
     * Updating product by MODEL
     * @param $data
     */
    public function updateProduct($data){
        $id_prom = $data['id_prom'];
        $model = $data['model'];
        $id_group_prom = $data['id_group_prom'];
        $this->db->query("UPDATE `" . DB_PREFIX . "prom_product` 
        SET `id_prom`='$id_prom', `id_group_prom`='$id_group_prom' WHERE `model`='$model'");
    }

    /**
     * Checking is category exist by prom ID
     * @param $id_prom
     * @return bool
     */
    public function isCategoryExist($id_prom){
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "prom_category` WHERE `id_prom` LIKE '$id_prom'");
        return $result->num_rows > 0 ? true : false;
    }

    /**
     * Adding the category
     * @param $data
     */
    public function insertCategory($data){
        $id_prom = $data['id_prom'];
        $name = $data['name'];
        $parent_id_prom = $data['parent_id_prom'];
        $category_id = $data['category_id'];
        $parent_id = $data['parent_id'];
        $this->db->query("INSERT INTO `" . DB_PREFIX . "prom_category` (id_prom, name, parent_id_prom, category_id, parent_id) 
        VALUES('$id_prom', '$name', '$parent_id_prom', '$category_id', '$parent_id')");
    }

    /**
     * Updating the category
     * @param $data
     */
    public function updateCategory($data){
        $id_prom = $data['id_prom'];
        $name = $data['name'];
        $parent_id_prom = $data['parent_id_prom'];
        $category_id = $data['category_id'];
        $parent_id = $data['parent_id'];
        $this->db->query("UPDATE `" . DB_PREFIX . "prom_category` 
        SET `name`='$name', `parent_id_prom`='$parent_id_prom', `category_id`='$category_id', `parent_id`='$parent_id' WHERE `id_prom` LIKE '$id_prom'");
    }

    /**
     * Getting category id and parent_id by prom name
     * @param $name
     * @return array []
     */
    public function getCategoryIdAndParentIdByName($name){
        $result = $this->db->query("SELECT " . DB_PREFIX . "category.category_id, " . DB_PREFIX . "category.parent_id FROM " . DB_PREFIX . "category LEFT JOIN " . DB_PREFIX . "category_description ON " . DB_PREFIX . "category.category_id = " . DB_PREFIX . "category_description.category_id WHERE " . DB_PREFIX . "category_description.name LIKE '$name'");
        $value = array();
        if ($result->num_rows > 0) {
            foreach($result->rows as $row) {
                $value['category_id'] = $row['category_id'];
                $value['parent_id'] = $row['parent_id'];
            }
        }
        return $value;
    }
}