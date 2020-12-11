<?php

class ControllerExtensionModuleExportYml extends Controller {
	private $error = array();

    /**
     * Installing additional tables = !!! That's function working only from Admin Controller !!! =
     */
    public function install() {
        $this->load->model('extension/module/export_yml');
        $this->model_extension_module_export_yml->installPromCategoryTable();
        $this->model_extension_module_export_yml->installPromProductTable();
    }

    /**
     * Removing additional tables = !!! That's function working only from Admin Controller !!! =
     */
    public function uninstall() {
        $this->load->model('extension/module/export_yml');
        $this->model_extension_module_export_yml->uninstallPromCategoryTable();
        $this->model_extension_module_export_yml->uninstallPromProductTable();
    }

	public function index() {

		$this->load->language('extension/module/export_yml');
		$this->load->model('setting/setting');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_export_yml', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/export_yml', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/export_yml', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_export_yml_status'])) {
			$data['module_export_yml_status'] = $this->request->post['module_export_yml_status'];
		} else {
			$data['module_export_yml_status'] = $this->config->get('module_export_yml_status');
		}

        if (isset($this->request->post['module_export_yml_import'])) {
            $data['module_export_yml_import'] = $this->request->post['module_export_yml_import'];
        } else {
            $data['module_export_yml_import'] = $this->config->get('module_export_yml_import');
        }

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['user_token'] = $this->session->data['user_token'];

		$this->response->setOutput($this->load->view('extension/module/export_yml', $data));
	}

    /**
     * Importing and parsing YML link from Prom, storing the data in DB
     */
	public function import_xml(){
        $this->load->model('extension/module/export_yml');
        $this->load->language('extension/module/export_yml');
        $json = array();
        // Check user has permission
        if (!$this->user->hasPermission('modify', 'extension/module/export_yml')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if ($_POST["xml_url"]){
//            $xml_url = $_POST["xml_url"];
//            $xml = simplexml_load_file($xml_url) or die("feed not loading");

            $this->parseCategoriesCsv('/home/h63053/data/www/optovik.shop/admin/controller/extension/module/groups.csv');
            $this->parseProductsCsv('/home/h63053/data/www/optovik.shop/admin/controller/extension/module/products.csv');
//            $this->parseCategories($xml);
//            $this->parseProducts($xml);
        }
        if (!$json) {
            $json['success'] = $this->language->get('text_import_success');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
        $this->session->data['success'] = $this->language->get('text_success');
    }

    private function parseCategoriesCsv($csvFile){
        $row = 1;
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            $nameField = 0;
            $idGroupField = 0;
            $idParentField = 0;
            while (($data = fgetcsv($handle, 0, "|")) !== FALSE) {
                $categoryData = array();
                $num = count($data);
                for ($c=0; $c < $num; $c++) {
                    if ($row == 1){
                        if (strcmp($data[$c], 'Название_группы') == 0) {
                            $nameField = $c;
                        }
                        elseif (strcmp($data[$c],'Идентификатор_группы') == 0) {
                            $idGroupField = $c;
                        }
                        elseif (strcmp($data[$c], 'Идентификатор_родителя') == 0) {
                            $idParentField = $c;
                        }
                    }else {
                        if ($c == $nameField) { //Name Group
                            $categoryData['name'] = $data[$c];
                        }elseif ($c == $idGroupField) { //Id Group
                            $categoryData['id_prom'] = $data[$c];
                            $categoryData['parent_id_prom'] = 0;
                        }elseif ($c == $idParentField) { //Parent Id Group
                            $categoryData['parent_id_prom'] = $data[$c];
                        }
                    }
                }
                if ($row > 1) $this->addCategory($categoryData);
                $row++;
            }
            fclose($handle);
        }
    }

    private function parseProductsCsv($csvFile){
        $row = 1;
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            $modelField = 0;
            $idProductField = 0;
            $idCategoryField = 0;
            while (($data = fgetcsv($handle, 0, "|")) !== FALSE) {
                $productData = array();
                $num = count($data);
                for ($c=0; $c < $num; $c++) {
                    if ($row==1){
                        if (strcmp($data[$c], 'Код_товара') == 0) {
                            $modelField = $c;
                        }
                        elseif (strcmp($data[$c], 'Идентификатор_товара') == 0) {
                            $idProductField = $c;
                        }
                        elseif (strcmp($data[$c], 'Идентификатор_группы') == 0) {
                            $idCategoryField = $c;
                        }
                    }else {
                        if ($c == $modelField) { //Model
                            $productData['model'] = $data[$c];
                        }elseif ($c == $idProductField) { //Id Product
                            $productData['id_prom'] = $data[$c];
                        }elseif ($c == $idCategoryField) { //Id Category (Group Prom Id)
                            $productData['id_group_prom'] = $data[$c];
                        }
                    }
                }
                if ($row > 1) $this->addProduct($productData);
                $row++;
            }
            fclose($handle);
        }
    }

    /**
     * Parsing XML and adding products to DB
     * @param SimpleXMLElement Object
     */
    private function parseProducts($xml){
        $catalogue = $xml->shop->offers;
        $productData = array();
        foreach ($catalogue as $offers) {
            foreach ($offers as $product) {
                foreach ($product->attributes() as $key => $value) {
                    if ($key == 'id') $productData['id_prom'] = $value;
                }
                $productData['model'] = $product->vendorCode;
                $productData['id_group_prom'] = $product->categoryId;
                $this->addProduct($productData);
            }
        }
    }

    /**
     * Parsing XML and adding categories to DB
     * @param SimpleXMLElement Object
     */
    private function parseCategories($xml){
        $catalogue = $xml->shop->categories;
        $categoryData = array();
        foreach ($catalogue as $categories) {
            foreach ($categories as $category) {
                if ($category != null){
                    $categoryData['name'] = $category;
                    foreach ($category->attributes() as $key => $value) {
                        $categoryData['parent_id'] = 0;
                        if ($key == 'id') {
                            $categoryData['id_prom'] = $value;
                            $categoryData['parent_id_prom'] = 0;
                        }
                        elseif ($key == 'parentId') {
                            $categoryData['parent_id_prom'] = (int)$value;
                        }
                    }
                    $this->addCategory($categoryData);
                }
            }
        }
    }

    /**
     * Adding or updating a product to DB
     * @param $productData
     */
    private function addProduct($productData){
        if( $this->model_extension_module_export_yml->isProductExist($productData['model']) ) {
            $this->model_extension_module_export_yml->updateProduct($productData);
        }else {
            $this->model_extension_module_export_yml->insertProduct($productData);
        }
    }

    /**
     * Adding or updating a category to DB
     * @param $categoryData
     * @return bool
     */
    private function addCategory($categoryData){
        $isError = true;
        $ids = $this->model_extension_module_export_yml->getCategoryIdAndParentIdByName($categoryData['name']);
        $categoryData['category_id'] = 0;
        $categoryData['parent_id'] = 0;

        if(isset($ids['category_id'])) $categoryData['category_id'] = $ids['category_id'];
        if(isset($ids['parent_id'])) $categoryData['parent_id'] = $ids['parent_id'];

        if ($categoryData['category_id'] > 0) $isError = false;
        if( $this->model_extension_module_export_yml->isCategoryExist($categoryData['id_prom']) ) {
            $this->model_extension_module_export_yml->updateCategory($categoryData);
        }else {
            $this->model_extension_module_export_yml->insertCategory($categoryData);
        }
        return$isError;
    }

    /**
     * Validating
     * @return bool
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/export_yml')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

}
