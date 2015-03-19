<?php
  
namespace Core;
class Files
{
    static function GetFileMimeType($fileName)
    {
        $open_bit = \finfo_open(FILEINFO_MIME_TYPE);
        return \finfo_file($open_bit, $fileName);
    }
    
    static function CheckFile($fileName, $params = null)
    {
        if (!file_exists($fileName))
            return false;
            
        $paramsCheck = true;
        if (!is_array($params))
        {
            $params = array($params);
        }
        foreach($params as $param => $value)
        {
            if (!$paramsCheck)
                break;
            switch (strtolower($param))
            {
                case 'mime':
                case 'mime-type':
                case 'mimetype':
                case 'mime type':
                    $file_mime = strtolower(self::GetFileMimeType($fileName));
                    if (is_array($value))
                    {
                        $checkTypeRes = false;
                        foreach($value as $checkType)
                        {
                            if ($file_mime == strtolower($checkType))
                            {
                                $checkTypeRes = true;
                                break;
                            }
                        }
                        if (!$checkTypeRes)
                            return false;
                    }
                    else
                    {
                        if ($file_mime != strtolower($value))
                            return false;
                    }
                    break;
                case 'ext':
                case 'extens':
                case 'extension':
                    $parseName = self::ParseFileName($fileName);
                    if (!((is_array($value) && in_array(strtolower($parseName['extension']), $value)) || (strtolower($parseName['extension']) == strtolower($value))))
                    {
                        return false;
                    }
                    break;
            }
        }
        return $paramsCheck;
    }
    
    static function ParseFileName($fileName)
    {
        $result = array('dirname'=>'', 'basename'=>'', 'extension'=>'', 'filename'=>'');
        if (!empty($fileName))
        {
            $dirPos = strrpos($fileName, '\\');
            $tmp = strrpos($fileName, '/');
            if ($tmp > $dirPos)
                $dirPos = $tmp;
            if ($dirPos !== false)
                $result['dirname'] = substr($fileName, 0, $dirPos + 1);
            else
                    $dirPos = -1;
            $result['basename'] = substr($fileName, $dirPos + 1);
            $extPos = strrpos($fileName, '.');
            if ($extPos !== false)
            {
                $result['filename'] = substr($fileName, $dirPos + 1, ($extPos - $dirPos) - 1);
                $result['extension'] = substr($fileName, $extPos + 1);
            }
            else
                $result['filename'] = substr($fileName, $dirPos + 1);
            }
        return $result;
    }
}



?>
