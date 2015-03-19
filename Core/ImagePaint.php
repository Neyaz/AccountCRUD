<?php
namespace Core;
class ImagePaint
{
    private $_Image;
    private $_ImageFileFilter = array('image/jpeg', 'image/png', 'image/gif');
    private $_ImageName;
    private $_ImageType;
    
    public function __construct($fileName = null)
    {
        if (!empty($fileName))
            $this->OpenImage($fileName);
    }
    
    /*  */
    public function FileName()
    {
        return $this->_ImageName;
    }
    /*  */
    
    public function CreateImage($params = array())
    {
        if (!is_int($params['width']))
            $params['width'] = 1;
        if (!is_int($params['height']))
            $params['width'] = 1;
        $this->_Image = imagecreatetruecolor($params['width'], $params['height']);
        imageAlphaBlending($this->_Image, false);
        imageSaveAlpha($this->_Image, true);
        $this->_ImageName = '';
        return true;
    }
    
    public function OpenImage($imageFileName)
    {
        if (\Core\Files::CheckFile($imageFileName, array('mime-type' => $this->_ImageFileFilter)))
        {
            $this->_ImageType = strtolower(\Core\Files::GetFileMimeType($imageFileName));
            $this->_ImageName = $imageFileName;
            switch ($this->_ImageType)
            {
                case 'image/jpeg':
                    $this->_Image = imagecreatefromjpeg($imageFileName);
                    break;
                case 'image/png':
                    $this->_Image = imagecreatefrompng($imageFileName);
                    break;
                case 'image/gif':
                    $this->_Image = imagecreatefromgif($imageFileName);
                    break;
            }
            if (!isset($this->_Image) || empty($this->_Image))
            {
                $this->_ImageType = '';
                $this->_ImageName = '';
                return false;
            }
            imageAlphaBlending($this->_Image, false);
            imageSaveAlpha($this->_Image, true);
        }
        else
            return false;
        return true;
    }
    
    public function ImageSize()
    {
        if (empty($this->_Image))
            return false;
        $x = imageSX($this->_Image);
        $y = imageSY($this->_Image);
        return array(0 => $x, 'width' => $x, 'x' => $x, 1 => $y, 'height' => $y, 'y' => $y);
    }
    
    public function ResizeImage($params = array())
    {
        if (empty($this->_Image))
            return false;
        $reSize = array('x' => 0, 'y' => 0);
        if (isset($params['width']))
        {
            $reSize['x'] = (int)$params['width'];
        }
        elseif (isset($params['x']))
        {
            $reSize['x'] = (int)$params['x'];
        }
        if (isset($params['height']))
        {
            $reSize['y'] = (int)$params['height'];
        }
        elseif (isset($params['y']))
        {
            $reSize['y'] = (int)$params['y'];
        }

        if ($reSize['x'] > 0 || $reSize['y'] > 0)
        {
            $vars = array();
            $vars['proportional'] = (isset($params['proportional']) && ($params['proportional'] == 1 || $params['proportional'] == 'yes' || $params['proportional'] == 'y' || $params['proportional'] == 'on')? 1 : 0);
            $size = $this->ImageSize();
            $newSize = array('x' => 0, 'y' => 0);
            if ($vars['proportional'])
            {
                $ratio = $size['x'] / $size['y'];
                if ($size['x'] > $size['y'])
                {
                    $newSize['x'] = $reSize['x'];
                    $newSize['y'] = round($reSize['x'] / $ratio);
                    if ($newSize['y'] > $reSize['y'])
                    {
                        $newSize['y'] = $reSize['y'];
                        $newSize['x'] = round ($reSize['y'] * $ratio);
                    }
                }
                else
                {
                    $newSize['y'] = $reSize['y'];
                    $newSize['x'] = round ($reSize['y'] * $ratio);
                    if ($newSize['x'] > $reSize['x'])
                    {
                        $newSize['x'] = $reSize['x'];
                        $newSize['y'] = round($reSize['x'] / $ratio);
                    }
                }
                /*
                if (($size['x'] > $size['y'] && $reSize['x'] > 0) || ($size['x'] < $size['y'] && ($reSize['y'] == 0 && $reSize['x'] > 0)))
                {
                    $scale = ($reSize['x'] * 100) / $size['x'];
                    $newSize['x'] = $reSize['x'];
                    $newSize['y'] = round(($scale * $size['y']) / 100);
                }
                else
                {
                    $scale = ($reSize['y'] * 100) / $size['y'];
                    $newSize['y'] = $reSize['y'];
                    $newSize['x'] = round(($scale * $size['x']) / 100);
                }
                */
            }
            else
            {
                if ($reSize['x'] > 0)
                    $newSize['x'] = $reSize['x'];
                else
                    $newSize['x'] = $size['x'];
                    
                if ($reSize['y'] > 0)
                    $newSize['y'] = $reSize['y'];
                else
                    $newSize['y'] = $size['y'];
            }
            
            if ($newSize['x'] != $size['x'] && $newSize['y'] != $size['y'])
            {
                $dstImage = imagecreatetruecolor($newSize['x'], $newSize['y']);
                imageAlphaBlending($dstImage, false);
                imageSaveAlpha($dstImage, true);
                if (imagecopyresampled($dstImage, $this->_Image, 0, 0, 0, 0, $newSize['x'], $newSize['y'], $size['x'], $size['y']))
                {
                    imagedestroy($this->_Image);
                    $this->_Image = $dstImage;
                    return true;
                }
                else
                {
                    imagedestroy($dstImage);
                    return false;
                }
            }
            else
                return true;
        }
        return false;
    }
    
    function CropImage ($params = array())
    {
        if (empty($this->_Image))
            return false;
        $size = $this->ImageSize();
        // параметры по умолчанию
        $cropSize = array('from_x' => 0, 'from_y' => 0, 'to_x' => $size['width'], 'to_y' => $size['height'], 'width' => $size['width'], 'height' => $size['height']);
        unset($size);

        // заполним параметры 
        if (isset($params['from_x']))
            $cropSize['from_x'] = (int)$params['from_x'];
        if (isset($params['from_y']))
            $cropSize['from_y'] = (int)$params['from_y'];
        if (isset($params['to_x']))
            $cropSize['to_x'] = $params['to_x'];
        if (isset($params['to_y']))
            $cropSize['to_y'] = $params['to_y'];
        
        // меняем местами координаты, если начало больше конца
        if ($cropSize['from_x'] > $cropSize['to_x'])
        {
            $tmp = $cropSize['from_x'];
            $cropSize['from_x'] = $cropSize['to_x'];
            $cropSize['to_x'] = $tmp;
        }
        if ($cropSize['from_y'] > $cropSize['to_y'])
        {
            $tmp = $cropSize['from_y'];
            $cropSize['from_y'] = $cropSize['to_y'];
            $cropSize['to_y'] = $tmp;
        }
        
        if (isset($params['checkCoord']) && $params['checkCoord'])
        {
            // меняем координаты, если одна из координат выходит за рамки
            if ($cropSize['from_x'] > $cropSize['width'])
            {
                $cropSize['from_x'] = $cropSize['width'];
                $cropSize['to_x'] = $cropSize['width'];
            }
            elseif ($cropSize['to_x'] > $cropSize['width'])
                $cropSize['to_x'] = $cropSize['width'];
        
            if ($cropSize['from_y'] > $cropSize['height'])
            {
                $cropSize['from_y'] = $cropSize['height'];
                $cropSize['to_y'] = $cropSize['height'];
            }
            elseif ($cropSize['to_y'] > $cropSize['height'])
                $cropSize['to_y'] = $cropSize['height'];
        }
        
        $cropSize['crop_width'] = $cropSize['to_x'] - $cropSize['from_x'];
        $cropSize['crop_height'] = $cropSize['to_y'] - $cropSize['from_y'];
        
        if (isset($params['checkRatio']) && $params['checkRatio'] && isset($params['ratio']))
        {
            // Проверяем соотношение сторон
            $ratio = $params['ratio'];
            $priority = $params['ratio_priority'];
            if ($ratio > 1)
            {
                // ширина больше высоты
                $ratioWidth = round($cropSize['crop_height'] * $ratio);
                // смотрим, если соотношение неверно (+/- 1 для исключения погрешности округления) тогда выставляем новое соотношение
                if ($ratioWidth < $cropSize['crop_width'] - 1 || $ratioWidth > $cropSize['crop_width'] + 1)
                {
                    if ($priority == 'width')
                    {
                        // приоритет по ширене
                        $cropSize['crop_height'] = $this->GetRatioSize(array('ratio' => $ratio, 'width' => $cropSize['crop_width']));
                        $cropSize['to_y'] = $cropSize['from_y'] + $cropSize['crop_height'];
                        if (isset($params['checkCoord']) && $params['checkCoord'])
                        {
                            if ($cropSize['crop_height'] > $cropSize['height'])
                            {
                                $cropSize['crop_height'] = $cropSize['height'];
                                $cropSize['to_y'] = $cropSize['from_y'] + $cropSize['crop_height'];
                                $cropSize['crop_width'] = $this->GetRatioSize(array('ratio' => $ratio, 'height' => $cropSize['crop_height']));
                                $cropSize['to_x'] = $cropSize['from_x'] + $cropSize['crop_width'];
                            }
                        }
                    }
                    else
                    {
                        // Приоритет по выссоте (по умоляанию)
                        $cropSize['crop_width'] = $this->GetRatioSize(array('ratio' => $ratio, 'height' => $cropSize['crop_height']));
                        $cropSize['to_x'] = $cropSize['from_x'] + $cropSize['crop_width'];
                        if (isset($params['checkCoord']) && $params['checkCoord'])
                        {
                            if ($cropSize['crop_width'] > $cropSize['width'])
                            {
                                $cropSize['crop_width'] = $cropSize['width'];
                                $cropSize['to_x'] = $cropSize['from_x'] + $cropSize['crop_width'];
                                $cropSize['crop_height'] = $this->GetRatioSize(array('ratio' => $ratio, 'width' => $cropSize['crop_width']));
                                $cropSize['to_y'] = $cropSize['from_y'] + $cropSize['crop_height'];
                            }
                        }
                    }
                }
            }
        }
        
        // Вырезаем изображение
        $dstImage = imagecreatetruecolor($cropSize['crop_width'], $cropSize['crop_height']);
        imageAlphaBlending($dstImage, false);
        imageSaveAlpha($dstImage, true);
        if (imagecopyresampled($dstImage, $this->_Image, 0, 0, $cropSize['from_x'], $cropSize['from_y'], $cropSize['crop_width'], $cropSize['crop_height'], $cropSize['crop_width'], $cropSize['crop_height']))
        {
            imagedestroy($this->_Image);
            $this->_Image = $dstImage;
            return true;
        }
        else
        {
            imagedestroy($dstImage);
        }
        return false;
    }
    
    private function GetRatioSize($params = array())
    {
        if (isset($params['ratio']) && (isset($params['width']) || isset($params['height'])))
        {
            if (isset($params['width']))
                return round($params['width'] / $params['ratio']);
            else
                return round($params['height'] * $params['ratio']);
        }
        return false;
    }
    
    function Close()
    {
        if (!empty($this->_Image))
            imagedestroy($this->_Image);
        unset($this);
    }
    
    function Save()
    {
        switch ($this->_ImageType)
        {
            case 'image/jpeg':
                if (imagejpeg($this->_Image, $this->_ImageName))
                    return true;
            case 'image/png':
                if (imagepng($this->_Image, $this->_ImageName))
                    return true;
            case 'image/gif':
                if (imagegif($this->_Image, $this->_ImageName))
                    return true;
        }
        return false;
    }
    
    function SaveAs($fileName, $fileType = '')
    {
        $result = false;
        
        if (!in_array(strtolower($fileType), $this->_ImageFileFilter))
            $fileType = 'image/png';

        switch ($fileType)
        {
            case 'image/jpeg':
                if (imagejpeg($this->_Image, $fileName))
                    $result = true;
                break;
            case 'image/png':
                if (imagepng($this->_Image, $fileName))
                    $result = true;
                break;    
            case 'image/gif':
                if (imagegif($this->_Image, $fileName))
                    $result = true;
                break;
        }
        
        if ($result)
            $this->_ImageName = $fileName;
            
        return $result;
    }
}

?>
