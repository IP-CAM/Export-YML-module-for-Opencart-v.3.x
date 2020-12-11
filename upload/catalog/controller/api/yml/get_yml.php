<?php
/**
 * Class ControllerApiYmlGetYml
 *
 *
 * Returned YML for the all goods
 */

class ControllerApiYmlGetYml extends Controller
{
    private $YML_KEY_PROM;

    /**
     *
     */
    public function index()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $this->YML_KEY_PROM = 'ajshdgDHGjH82376KJHKjhgj';

        $this->load->language('api/yml/get_yml');
        $this->load->model('api/yml/get_yml');
        unset($this->session->data['get_yml']);

        $json = array();
        $json['success'] = sprintf($this->language->get('error'));

        if (isset($_GET['key']) && $_GET['key'] == $this->YML_KEY_PROM) {
//            $yGenerator = new YGenerator();
//            $xml = $yGenerator->getPromYml();
            $xml = $this->getPromYml();
            Header('Content-type: text/xml');
            print($xml->asXML());
        } else echo '-= fuck off =-';


    }

    private function getPromYml()
    {

//        if (file_exists($this->logFilename)) unlink($this->logFilename);
//        $d ='<!DOCTYPE html>
//        <html>
//        <head>
//        <style>
//        table {
//            font-family: arial, sans-serif;
//            border-collapse: collapse;
//            width: 100%;
//        }
//        td, th {
//            border: 1px solid #dddddd;
//            text-align: left;
//            padding: 8px;
//        }
//        tr:nth-child(even) {
//            background-color: #dddddd;
//            }
//            </style>
//            </head>
//            <body>';
//        $d .= 'Ссылка Prom. Последнее обновление: ' . date("d-m-Y") . ', ' . date("h:i:sa");
//        $this->writeLogToFile($this->logFilename, '<b>' . $d . '</b>' . '<br>');
        $countRejectedProducts = 0;
        $countAddedProducts = 0;

        $xml = new SimpleXMLElement('<yml_catalog/>');
        $dt = date("Y-m-d");
        $tm = date("H:i");
        $xml->addAttribute("date", $dt . ' ' . $tm);


        $shop = $xml->addChild('shop');
        $shop->addChild('name', "Optovik");
        $shop->addChild('company', "Optovik");
        $shop->addChild('url', "https://optovik.biz.ua/");
        $shop->addChild('version', "2.3.0.2.3");

        $currencies = $shop->addChild('currencies');
        $currency = $currencies->addChild('currency');
        $currency->addAttribute("id", "UAH");
        $currency->addAttribute("rate", "1");

        // #### Categories Section ####
        $categories = $shop->addChild('categories');

        $resultCategories = $this->model_api_yml_get_yml->getCategories();
        if ($resultCategories) {
            foreach($resultCategories->rows as $row) {

                $categoryPromIds = $this->model_api_yml_get_yml->getCategoryPromId($row['name']);
                $categoryPromId = '';
                $categoryParentPromId = '';
                if ($categoryPromIds) {
                    foreach($categoryPromIds->rows as $catProm) {
                        if (strlen($catProm['id_prom']) > 0) $categoryPromId = $catProm['id_prom'];
                        if (strlen($catProm['parent_id_prom']) > 0) $categoryParentPromId = $catProm['parent_id_prom'];
                    }
                }

                if (strlen($categoryPromId) > 0){
                    $category = $categories->addChild('category', $row['name']);
                    $category->addAttribute("id", $categoryPromId);
                    if (strlen($categoryParentPromId) > 0) $category->addAttribute("parentId", $categoryParentPromId);
                }

//                $parentId = 777; //$this->getParentIdCategory($con, $row['category_id']);
//                if ($parentId != 0) {
//                    $category->addAttribute("parentId", $parentId);
//                }
            }
        }

        //#### End Categories Section ####

        // #### Offers Section ####
        $offers = $shop->addChild('offers');
        $resultProducts = $this->model_api_yml_get_yml->getProducts();
        if ($resultProducts) {
//            $this->writeLogToFile($this->logFilename, '<b>Всего товаров: ' . $result->num_rows . '</b>' . '<br>');
//            $this->writeLogToFile($this->logFilename, '<b style="color: #0000ff;">Товары, не попавшие в YML:</b></br>');
//            $this->beginTable($this->logFilename);
            $totalProducts = 0;
            foreach($resultProducts->rows as $row) {
                $productId = $row['product_id'];
                $model = $row['model'];
                $manufacturerId = $row['manufacturer_id'];
                $stock_quantity = $row['quantity'];

                $totalProducts++;

                // #### Attribute section ####
                $listAttributes = array();
                $nameProduct = $this->model_api_yml_get_yml->getProductName($productId);

                $resultAttribute = $this->model_api_yml_get_yml->getAttributes($productId);
                if ($resultAttribute) {
                    foreach($resultAttribute->rows as $row3) {
                        $data = array();
                        $nameAttribute = $this->model_api_yml_get_yml->getNameAttributeById($row3['attribute_id']);
                        if ($nameAttribute) $data['nameAttribute'] = $nameAttribute;
                        $valueAttribute = $row3['text'];
                        $data['valueAttribute'] = $valueAttribute;
                        $data['sortOrder'] = $this->model_api_yml_get_yml->getAttributeSortOrder($row3['attribute_id']);

                        array_push($listAttributes, $data);
                    }
                }

                $offer = $offers->addChild('offer');

                //Checking if promId exist in current product - add it to YML, else - add code from 1C
                $productPromId = $this->model_api_yml_get_yml->getProductPromId($productId);
                if ($productPromId){
                    $offer->addAttribute("id", $productPromId);
                } else $offer->addAttribute("id", $productId);


                $offer->addAttribute("available", "true");
                $textUrl = 'https://optovik.shop/index.php?route=product/product&amp;product_id=' . $productId;
                $offer->addChild('url', $textUrl);


                $priceId = 4; //Розничный тип цены
                $priceSpecial = $this->model_api_yml_get_yml->getSpecialPrice($productId, $priceId);
                if ($priceSpecial) {
                    $offer->addChild('oldprice', $row['price']);
                    $offer->addChild('price', $priceSpecial);
                } else {
                    $offer->addChild('price', $row['price']);
                }

                $offer->addChild('currencyId', 'UAH');

                //Checking if category prom Id exist in current product - add it to YML, else - add code from 1C
                $productPromCategoryId = $this->model_api_yml_get_yml->getProductPromCategoryId($productId);
                if ($productPromCategoryId) $offer->addChild('categoryId', $productPromCategoryId);
                else {
                    $categoryId = $this->model_api_yml_get_yml->getCategoryId($productId);
                    if ($categoryId) $offer->addChild('categoryId', $categoryId);
                    else $offer->addChild('categoryId', 0);
                }

                $img = 'https://optovik.shop/image/' . $row['image'];
                $offer->addChild('picture', $img);

                $listImages = $this->model_api_yml_get_yml->getImages($productId);
                if ($listImages) {
                    foreach($listImages->rows as $row7) {
                        $img = 'https://optovik.shop/image/' . $row7['image'];
                        $offer->addChild('picture', $img);
                    }
                }

                $vendorName = $this->model_api_yml_get_yml->getVendorName($manufacturerId);
                $offer->addChild('vendor', $vendorName);
                $offer->addChild('vendorCode', $model);
                $offer->addChild('quantity_in_stock', $stock_quantity);
                $offer->addChild('pickup', "true");
                $offer->addChild('delivery', "true");

                $productDescription = $this->model_api_yml_get_yml->getProductDescription($productId);
                if ($productDescription) {
                    foreach($productDescription->rows as $row2) {
//                        $text = $this->removeHtmlHeaders($row2['description']);
                                $text = $row2['description'];
                        $name = $offer->addChild('name', htmlspecialchars($row2['name']));
                        if (strlen(trim($text)) == 0) {
                            $offer->addChild('description', $name);
                        } else {
                            $offer->addChild('description', $text);
                        }
                    }
                }

//              Sorting array by `sortOrder` field
                for ($i = 0; $i < count($listAttributes); $i++) {
                    for ($j = $i + 1; $j < count($listAttributes); $j++) {
                        if ($listAttributes[$i]['sortOrder'] > $listAttributes[$j]['sortOrder']) {
                            $temp = $listAttributes[$j];
                            $listAttributes[$j] = $listAttributes[$i];
                            $listAttributes[$i] = $temp;
                        }
                    }
                }

                ### Adding attributes
                for ($i = 0; $i < count($listAttributes); $i++) {
                    $valueAttribute = trim($listAttributes[$i]['valueAttribute']);
                    $valueAttribute = htmlspecialchars($valueAttribute);
                    $param = $offer->addChild('param', $valueAttribute);
                    $param->addAttribute('name', $listAttributes[$i]['nameAttribute']);
                }
                $param = $offer->addChild('param', $model);
                $param->addAttribute('name', 'Артикул');
                if ($stock_quantity > 800) {
                    $param = $offer->addChild('param', 'Товар под заказ. Срок до 5 дней. Предоплата 20%');
                    $param->addAttribute('name', 'Доставка/Оплата');
                }
                $countAddedProducts++;
//                    }else{
//                        $this->tr .= $this->bodyTable(++$countRejectedProducts, $model, $nameProduct, $row['image'], $listAttributes, $stock_quantity);
//                    }
//                }else {
//                    $this->tr .= $this->bodyTable(++$countRejectedProducts, $model, $nameProduct, $row['image'], $listAttributes, $stock_quantity);
//                }
            }
//            $this->writeLogToFile($this->logFilename, $this->tr);
//            $this->endTable($this->logFilename, $countRejectedProducts, $countAddedProducts, $totalProducts);
        }
        return $xml;
    }

    private function beginTable($filename)
    {
        $tableHeader =
            '<table>
          <tr>
            <th>N</th>
            <th>Код</th>
            <th>Наименование</th>
            <th>Уценка</th>
            <th>Картинка</th>
            <th>Свойства</th>
            <th>Кол-во</th>
          </tr>';
        $this->writeLogToFile($filename, $tableHeader);
    }

    private function bodyTable($countRejectedProducts, $model, $nameProduct, $images, $listAttributes, $stock_quantity)
    {
        $str_img = strlen($images) > 8 ? 'есть' : 'нет';
        $str_markdown = strpos($nameProduct, 'УЦЕНКА') ? 'Уценка' : '-';
        $tr = '<tr>';
        $tr .= '<td>' . $countRejectedProducts . '</td>';
        $tr .= '<td>' . $model . '</td>';
        $tr .= '<td>' . $nameProduct . '</td>';
        $tr .= '<td>' . $str_markdown . '</td>';
        $tr .= '<td>' . $str_img . '</td>';
        $tr .= '<td>' . count($listAttributes) . '</td>';
        $tr .= '<td>' . $stock_quantity . '</td>';
        $tr .= '</tr>';
        return $tr;
    }

    private function endTable($filename, $countRejectedProducts, $countAddedProducts, $totalProducts)
    {
        $tableEnd =
            '</table>';
        $tableEnd .= '<br><b style="color: blue;">' . 'Всего : ' . $totalProducts . ' товаров</b>';
        $tableEnd .= '<br><b style="color: blue;">' . 'Из них добавлено в XML: ' . $countAddedProducts . ' товаров</b>';
        $tableEnd .= '<br><b style="color: blue;">' . 'Отклонено : ' . $countRejectedProducts . ' товаров</b>';
        $tableEnd .= '</body></html>';
        $this->writeLogToFile($filename, $tableEnd);
    }

    /**
     * @param $str
     * @return mixed
     */
    private function cutExtraCharacters($str)
    {
        $cyr = [
            ' кг', ' л', ' Вт', ' куб. м/ч', ' см', ' дБ'
        ];

        $str = str_replace($cyr, '', $str);
        return $str;
    }

    /**
     * @param $str
     * @return mixed
     */
    private function cutPunctuationMarks($str)
    {
        $cyr = [
            ',', '.', '-'
        ];

        $str = str_replace($cyr, ' ', $str);
        return $str;
    }




    /**
     * Removing HTML headers
     * @param $str
     * @return string
     */
    private function removeHtmlHeaders($str)
    {
        $str = preg_replace('/<html>/', '', $str);
        $str = preg_replace('/<\/html>/', '', $str);
        $str = preg_replace('/<body>/', '', $str);
        $str = preg_replace('/<\/body>/', '', $str);
        $str = preg_replace('/<head([^&]*)head>/', '', $str);

        $str = preg_replace('/style="[\S\s]*?"/', '', $str);
        $str = preg_replace('/<span[\S\s]*?>/', '', $str);
        $str = preg_replace('/<\/span>/', '', $str);
        $str = preg_replace('/<style([^&]*)style>/', '', $str);
        $str = preg_replace('/<meta([^&]*)\/>/', '', $str);
        $str = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $str);
        $str = str_replace('P.S. В случае отсутствия товара, оставьте заявку на нашем сайте!', '', $str);
        $str = str_replace('Характеристики и комплектация товара могут изменяться производителем без уведомления', '', $str);
        return $str;
    }

    /**
     * Removing HTML tags and other garbage
     * @param $str
     * @return string
     */
    private function removeTagsRozetka($str)
    {
        $str = preg_replace('/<html>/', '', $str);
        $str = preg_replace('/<\/html>/', '', $str);
        $str = preg_replace('/<body>/', '', $str);
        $str = preg_replace('/<\/body>/', '', $str);
        $str = preg_replace('/<head([^&]*)head>/', '', $str);
        $str = preg_replace('/style="[\S\s]*?"/', '', $str);
        $str = preg_replace('/<span[\S\s]*?>/', '', $str);
        $str = preg_replace('/<\/span>/', '', $str);
        $str = preg_replace('/<style([^&]*)style>/', '', $str);

        $str = preg_replace('/<meta([^&]*)\/>/', '', $str);
        // $str = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $str);
        $str = preg_replace('/<[^>]*>/', '', $str);
        $str = str_replace('P.S. В случае отсутствия товара, оставьте заявку на нашем сайте!', '', $str);
        $str = str_replace('Характеристики и комплектация товара могут изменяться производителем без уведомления', '', $str);
//    $str = $str.replaceAll("<[^>]*>", "");
        return $str;
    }


    /**
     * @param $filename
     * @param String $text_to_file
     */
    function writeLogToFile($filename, $text_to_file)
    {
        file_put_contents($filename, $text_to_file . '<br>', FILE_APPEND);
    }


}