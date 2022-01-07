<?php
    function createHeader(){
        $header = ['itemID', 'itemNumber', 'Type','Brand','Model','Size','Price','Sale_price','Description'];
        return $header;
    }
    function validatePrice($price){
        if(is_numeric($price)){
            $decimal_part = isset(explode('.', $price)[1]) ? explode('.', $price)[1] : 0;

            //check to make sure the decimal part only consists of at most 2 decimal 
            if($decimal_part > 99){
                return false;
            }else{
                return true;
            }
        }else{
            return false;

        }
    }

    function getHandleFromFile($mode){
        $handle = fopen('model/data.csv', $mode) or die("Can't open the file");
        return $handle;
    };
    function getAllRecords(){
        $data = '';
        $handle = getHandleFromFile('r');
        $header = fgetcsv($handle);
        $records = [];
        
        while($data = fgetcsv($handle)){            
            $records [] = $data;
        } 
        fclose($handle);
        return $records;
    }

    function getSingleRecord($item_number){
        $data = '';
        $handle = getHandleFromFile('r');
        while($data = fgetcsv($handle)){
            if($data[1] == $item_number){
                fclose($handle);
                return $data;
            }   
        }
        
        return false;
            
    }
    function getRecordsByBrand($brand){
        $records = getAllRecords();
        $result = [];
        for ($i=0; $i < count($records) ; $i++) { 
            if(trim($records[$i][3]) == $brand){
                $result [] = $records[$i];
            }
        }
        if(count($result) == 0){
            return false;
        }
        return $result;
    }

    function addRecord($type, $brand, $model, $size, $price, $sale_price = "", $description = ""){
        $handle = getHandleFromFile('a');
        $id = uniqid();
        $itemNumber = random_int(10000,99999);
        
        $new_record = [$id, $itemNumber, $type, $brand, $model, $size, $price, $sale_price, $description];
        fputcsv($handle, $new_record);
        fclose($handle);
    }

    function updateRecord($id, $itemNumber, $type, $brand, $model, $size, $price, $sale_price, $description){
        // get the current records and update the desired one
        $records = getAllRecords();

        for ($i=0; $i < count($records); $i++){ 
            if($records[$i][0] == $id){
                $records[$i] = [$id, $itemNumber, $type, $brand, $model, $size, $price, $sale_price, $description];
                break;
            }
        }
        
        // w+ will erase the current data of the file and allow to be written 
        $handle = getHandleFromFile('w+');
        $header = createHeader();

        fputcsv($handle, $header);
        foreach ($records as $record){
            fputcsv($handle, $record);
        }
        unset($record);
        fclose($handle);
    }

    function deleteRecord($id){
        $records = getAllRecords();
        for ($i=0; $i < count($records); $i++){ 
            if($records[$i][0] == $id){
                array_splice($records, $i, 1);
                break;
            }
        }
        $handle = getHandleFromFile('w+');

        $header = createHeader();
        fputcsv($handle, $header);
        foreach ($records as $record){
            fputcsv($handle, $record);
        }
        unset($record);
        fclose($handle);

    }
?>