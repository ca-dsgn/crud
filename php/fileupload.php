<?php

$path_up = "../";

include($path_up."php/helper.php");
 
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
		
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
		
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
	
    private $allowedExtensions = array();
    private $sizeLimit = 8388607;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size $postSize $uploadSize'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
		
		global $path_up;
		global $dblink;
		global $host;
		
        if (!is_writable($path_up.$uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
		
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($path_up.$uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
		
		/* EXTENDED BY CA */
		
		$upload_type = "bill_background";
		
		if (isset($_GET["upload_type"])) {
			
			$upload_type = $_GET["upload_type"];
		}
		
		switch ($upload_type) {
			
			case "bill_background":
			
				$filename = "bill_background";
				break;
				
			default:
				
				$filename = gen_id($filename);
				break;
		}
		
		$ext = strtolower($ext);
        
        if ($this->file->save($path_up.$uploadDirectory . $filename . '.' . $ext)) {
			
			$file_result = $host.$uploadDirectory.$filename.'.'.$ext;
			
			switch ($upload_type) {
				
				case "bill_background":
					
					$sql_test = "SELECT bill_background FROM user_setting WHERE name='bill_background' AND user=".$_COOKIE["id"];
					
					$test_result = mysqli_query($dblink,$sql_test);
					
					if (mysqli_num_rows($test_result) > 0) {
					
						$sql = "UPDATE user_setting SET value='".$file_result."' WHERE name='bill_background' AND user=".$_COOKIE["id"];
					}
					else {
						
						$sql = "INSERT INTO user_setting (user,name,value) VALUES ('".$_COOKIE["id"]."','bill_background','".$file_result."')";
					}
					
					mysqli_query($dblink,$sql);
				
					return array('success'=>true,
								 'file'=> $file_result
					);
					break;
				
				case "csv_import":
					
					$csv_file = $path_up.$uploadDirectory.$filename.'.'.$ext;
					
					$row=1;
					
					$fields = "(user,";
					$values = "";
					
					if (($handle = fopen($csv_file, "r")) !== FALSE) {
						
						while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
							
							$num = count($data);
							
							if ($row > 1) {
							
								$values.= "('".$_COOKIE["id"]."',";
							}
							for ($c=0; $c < $num; $c++) {
								
								if ($row == 1) {

									$fields.= translate_label_to_key($data[$c]).",";
								}
								else {

									$values.= "'".mysqli_real_escape_string($dblink,utf8_encode($data[$c]))."',";
								}
							}
							if ($row > 1) {
								
								$values = substr($values,0,strlen($values)-2)."'),";
							}
							$row++;
						}
						fclose($handle);
						
						$fields = rtrim($fields,",").")";
						$values = rtrim($values,",(");
					}
					
					$sql = "INSERT INTO address ".$fields." VALUES ".$values;
					
					mysqli_query($dblink,$sql) or die(print mysqli_error($dblink));
					
					$sql = "SELECT * FROM address WHERE user=".$_COOKIE["id"];
                
					$qresult = mysqli_query($dblink,$sql);
					
					$html = "";

					while($row = mysqli_fetch_array($qresult)) {

						$html.= get_html_template("address_item",$row);
					}
				
					return array('success'=>true,
								 'html'=>$html,
								 "addresses" => get_addresses("array"),
								 'file'=> $file_result
					);
					break;
					
			}
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }
}

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array();
// max file size in bytes

$upload_max = (int)ini_get('post_max_size');

if ((int)ini_get('upload_max_filesize') < (int)ini_get('post_max_size')) {
	
	$upload_max = (int)(ini_get('upload_max_filesize'));
}

$sizeLimit = $upload_max * 1024 * 1024;

$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

$upload_type = isset($_GET["upload_type"]) ? $_GET["upload_type"] : "bill_background";

switch ($upload_type) {
	
	case "bill_background":
	
		if (!file_exists($path_up."files/user/".$_COOKIE["id"]."/settings/")) {
			
			mkdir($path_up."files/user/".$_COOKIE["id"]."/settings/");
		}
		
		$result = $uploader->handleUpload("files/user/".$_COOKIE["id"]."/settings/");
		break;
		
	case "csv_import":
	
		if (!file_exists($path_up."files/user/".$_COOKIE["id"]."/import/")) {
			
			mkdir($path_up."files/user/".$_COOKIE["id"]."/import/");
		}
		
		$result = $uploader->handleUpload("files/user/".$_COOKIE["id"]."/import/");
		break;
}

echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);