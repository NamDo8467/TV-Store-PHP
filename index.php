<?php
    require 'model/functions.inc.php';
    include 'view/inc/header.phtml';
    $page = '';
    $item_number = '';
    $specificItem = '';
    $records = getAllRecords();
    $type = $brand = $model = $size = $price = $sale_price = $description = '';
    
    $set_of_type = ['LCD', 'LED', 'OLED', 'QLED' ];
    $set_of_brand = ['LG', 'Samsung', 'Toshiba', 'Sony','Sanyo', 'TCL', 'Philips', 'RCA', 'JVC', 'Hisense'];
    $set_of_size = ['32','40','43','50','55','60','65','70','75','80','85'];

    $price_error = '';
    $sale_price_error = '';

    $search_id = $filter_brand =  '';
    $search_result = $filter_result =  '';
    $search_and_filter_result = array();

    $host = "/comp1230/assignments/assignment3/";
    
  
    /* Getting the page to direct user to that page */
    if(isset($_GET['page'])){
        $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
        if($page == 'item'){
            $item_number = filter_input(INPUT_GET, 'item_number', FILTER_SANITIZE_STRING);
            $specificItem = getSingleRecord($item_number);
        }else if($page == 'updateRecord'){
            $record = $_GET['record']; 
            $update_id = $record['id'];
            $update_item_number = $record['item_number'];
            $update_type = $record['type'];
            $update_brand = $record['brand'];
            $update_model = $record['model'];
            $update_size = $record['size'];
            $update_price = $record['price'];
            $update_sale_price = $record['sale_price'];
            $update_description = $record['description'];

        }else if($page == 'deleteRecord'){
            $record = $_GET['record']; 
            $delete_id = $record['id'];
            if(array_key_exists('yes_button', $_POST)){
                deleteRecord($delete_id);
                header("Location: $host");
            }else if(array_key_exists('no_button', $_POST)){
                header("Location: $host");
                // $page = '';
                
                
            }
        }
    }


    /* Getting a new record and add it to the data.csv file */
    if(isset($_POST['type']) && isset($_POST['brand']) 
    && isset($_POST['model']) && isset($_POST['size']) && isset($_POST['price'])){
        $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
        $brand = filter_input(INPUT_POST, 'brand', FILTER_SANITIZE_STRING);
        $model = filter_input(INPUT_POST, 'model', FILTER_SANITIZE_STRING);
        $size = filter_input(INPUT_POST, 'size', FILTER_SANITIZE_STRING);
        $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_STRING);

        // The reason not to initialize sale_price and description
        //  outside is that they are not mandatory to have
        $sale_price = filter_input(INPUT_POST, 'sale_price', FILTER_SANITIZE_STRING);
        $sale_price == '' ? $sale_price = "" : null;
        
    
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $description == '' ? $description = "" : null;

        if($page == 'addRecord' || $page == 'updateRecord'){
            if(validatePrice($price)){
                $price_error = '';
                if(validatePrice($sale_price) || $sale_price == ''){
                    if($page == 'addRecord'){
                        addRecord($type, $brand, $model, $size, $price, $sale_price, $description);
                    }else if($page == 'updateRecord'){
                        updateRecord($update_id, $update_item_number,$type, $brand, $model, $size, $price, $sale_price, $description);
                    }
                    header("Location: $host");
                }else{
                    $sale_price_error = "Must be a number with 2 decimal places or less";
                }
                
            }else{
                $price_error = "Must be a number with 2 decimal places or less";
            }
            
        }
        
    }

    /* FILTER AND SEARCH FEATURES */
    if(isset($_GET['search_id'])){
        $search_id = filter_input(INPUT_GET, 'search_id', FILTER_SANITIZE_STRING);
        $search_result = getSingleRecord($search_id);
        
    }
    if(isset($_GET['filter_brand'])){
        $filter_brand = filter_input(INPUT_GET, 'filter_brand', FILTER_SANITIZE_STRING);
        $filter_result = getRecordsByBrand($filter_brand);
       
    }
    if($search_result && $filter_result){
        for ($i=0; $i < count($filter_result); $i++) { 
            if($search_result[0] == $filter_result[$i][0]){
                $records = array($search_result);;
                break;
            }else{
                $records = 'No result found';
            } 
        }
        
    }else if($search_result && !$filter_result){
        if($search_id && $filter_brand){
            $records = 'No result found';
        }else{
            $records = array($search_result);
        }
    }else if($filter_result && !$search_result){
        if($search_id && $filter_brand){
            $records = 'No result found';
        }else{
            $records = $filter_result;
        }
        
    }else if(!$search_result && !$filter_result){
        if($search_id || $filter_brand){
            $records = 'No result found';
        }
        
    }

    /* IMPORT FILE */
    if(isset($_POST['upload'])){
        $handle = fopen($_FILES['imported_file']['tmp_name'], "r");
        $header = fgets($handle);
        
        $new_data  = array();
        
        while($data = fgetcsv($handle)){
           $new_data [] = $data;
        }
        fclose($handle);
        
        // Get the file of server and erase everything
        $server_file_handle = getHandleFromFile('w+');

        //Create a header for the file in case the submitted file contains an invalid header
        $new_header = createHeader();
        fputcsv($server_file_handle, $new_header);

        for ($i=0; $i < count($new_data) ; $i++) { 
            fputcsv($server_file_handle, $new_data[$i]);
        }
        fclose($server_file_handle);
        header("Location: $host");
                
       
    }
        
    switch ($page){
        case '':
            include('view/main.phtml');
            break;
        case 'addRecord':
            include('view/addRecord.phtml');
            break;
        case 'item':
            include('view/specificItem.phtml');
            break;
        case 'updateRecord':
            include('view/updateRecord.phtml');
            break;
        case 'deleteRecord':
            include('view/deleteRecord.phtml');
            break;
        case 'importData':
            include('view/importData.phtml');
            break;
        default:
            include('view/error.phtml');
            break;
    }

    include 'view/inc/footer.phtml';

?>

<?php
    echo "<hr>";
    show_source(__FILE__);
?>
