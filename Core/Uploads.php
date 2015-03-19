<?php
  
namespace Core;
class Uploads
{
    static function GetUploadPath($path = null)
    {
        if (!empty($path) && \Core\Path::FileExist($path))
            return $path;
        return \Core\Path::BasePath();
    }
    
    static function GetFileMimeType($fileName)
    {
        $open_bit = \finfo_open(FILEINFO_MIME_TYPE);
        return \finfo_file($open_bit, $fileName);
    }
    
    static function CheckUpload($uploadName, $params = null)
    {
        $result = array();
        if (isset($_FILES[$uploadName]))
        {
            $files = array();
            if (!is_array($_FILES[$uploadName]['name']))
            {
                $files['name'] = array($_FILES[$uploadName]['name']);
                $files['tmp_name'] = array($_FILES[$uploadName]['tmp_name']);
                $files['error'] = array($_FILES[$uploadName]['error']);
                $files['type'] = array($_FILES[$uploadName]['type']);
                $files['size'] = array($_FILES[$uploadName]['size']);
            }
            else
                $files = $_FILES[$uploadName];
            
            for ($i = 0, $ic = count($files['name']); $i < $ic; $i++)
            {
                $result[$i]['tmp_name'] = $files['tmp_name'][$i];
                $result[$i]['name'] = $files['name'][$i];
                $result[$i]['error'] = $files['error'][$i];
                $result[$i]['type'] = $files['type'][$i];
                $result[$i]['size'] = $files['size'][$i];
                $result[$i]['check'] = true;
                
                if (!is_uploaded_file($result[$i]['tmp_name']))
                    $result[$i]['check'] = false;
                
                if ($result[$i]['check'] && \Core\Files::CheckFile($files['tmp_name'][$i], $params))
                    $result[$i]['check'] = true;
                else
                    $result[$i]['check'] = false;
            }
        }
        if (count($result) == 0)
            return false;
        return $result;
    }
    
    static function GenerateName($name, $idx = null)
    {
        $parsedName = \Core\Files::ParseFileName($name);
        return $parsedName['filename'].(isset($idx) && is_int($idx) ? '_'.$idx : '').'.'.$parsedName['extension'];
    }
    
    static function SaveUpload($uploadName, $params)
    {
        $path = self::GetUploadPath($params['save_path']);

        $result = self::CheckUpload($uploadName, $params);
        
        if ($result !== false)
        {
            if (!isset($result[0]))
            {
                $result = array($result);
            }
            
            $cnt = count($result);
            
            foreach($result as $key => $file)
            {
                $newName = self::GenerateName(((!empty($params['save_name'])) ? $params['save_name'] : $file['tmp_name']),($cnt == 1 ? '' : $key));
                $result[$key]['filename'] = \Core\Path::Combine($path, $newName);
                if ($file['check'] && move_uploaded_file($file['tmp_name'], $result[$key]['filename']))
                {
                    $result[$key]['check'] = true;
					$result[$key]['parse_name'] = \Core\Files::ParseFileName($result[$key]['filename']);
                }
                else
                {
                    $result[$key]['check'] = false;
                }
            }
            return $result;
        }
        else 
            return false;
    }
}
?>
