<?php

namespace Core\File;
use Core\Path;

class ImageFile extends File
{

    var $image;
    var $imageType;

    function Load()
    {
        $image_info = getimagesize($this->GetLocaton());
        $this->imageType = $image_info[2];

        if( $this->imageType == IMAGETYPE_JPEG )
            $this->image = imagecreatefromjpeg($this->GetLocaton());
        elseif( $this->imageType == IMAGETYPE_GIF )
            $this->image = imagecreatefromgif($this->GetLocaton());
        elseif( $this->imageType == IMAGETYPE_PNG )
            $this->image = imagecreatefrompng($this->GetLocaton());
    }

    public function PostProcessing()
    {
        if(!$this->filled || $this->upload)
            return;
        $this->Load($this->GetLocaton());
    }

    public function SetType($imageType)
    {
        $this->imageType = $imageType;
    }



    function Save($dir = null, $basename = null, $compression = 75)
    {
        if($this->upload)
        {
            parent::Save($dir, $basename);
            $this->AssignFile($this->GetLocaton());
            return;
        }

        $this->ApplyNewPath($dir, $basename);
        if($basename != null)
            $this->SetTypeByName($basename);

        switch($this->imageType)
        {
            case IMAGETYPE_JPEG:
                imagejpeg($this->image, $this->GetLocaton(), $compression);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->image, $this->GetLocaton());
                break;
            case IMAGETYPE_PNG:
                imagepng($this->image, $this->GetLocaton());
                break;

        }
        $this->AssignFile($this->GetLocaton());

    }

    function GetWidth()
    {
        return imagesx($this->image);
    }

    function GetHeight()
    {
        return imagesy($this->image);
    }

    /**
    * Прапорционально изменяет размеры изображения до указанной высоты
    *
    * @param int $height
    */
    function ResizeToHeight($height) {
        $ratio = $height / $this->GetHeight();
        $width = $this->GetWidth() * $ratio;
        $this->Resize($width,$height);
    }

    /**
    * Прапорционально изменяет размеры изображения до указанной ширины
    *
    * @param int $width
    */
    function ResizeToWidth($width) {
        $ratio = $width / $this->GetWidth();
        $height = $this->GetHeight() * $ratio;
        $this->Resize($width,$height);
    }

    /**
    * Меняет размер изображения по заданному множителю
    *
    * @param double $scale - множитель
    */
    function Scale($scale) {
        $width = $this->GetWidth() * $scale/100;
        $height = $this->GetHeight() * $scale/100;
        $this->Resize($width,$height);
    }

    /**
    * Изменяет размеры изображения
    *
    * @param int $width
    * @param int $height
    */
    function Resize($width, $height) {
        if($this->upload)
            throw new \Core\FileNotSavedException();

        $newImage = imagecreatetruecolor($width, $height);

        //*
        imagealphablending($newImage, false);
        imagesavealpha($newImage,true);

        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $width, $height, $transparent);
          //*/
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->GetWidth(), $this->GetHeight());
        $this->image = $newImage;
    }

    function Crop($width, $height)
    {
        $newImage = imagecreatetruecolor($width, $height);

        //*
        imagealphablending($newImage, false);
        imagesavealpha($newImage,true);

        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $nWidth, $height, $transparent);
          //*/
        imagecopy($newImage, $this->image, 0, 0, ($this->GetWidth() - $width)/2, ($this->GetHeight() - $height)/2, $width, $height);
        $this->image = $newImage;
    }

    private function SetTypeByName($basename)
    {
        $types = array(
                        "jpg" => IMAGETYPE_JPEG,
                        "gif" => IMAGETYPE_GIF,
                        "png" => IMAGETYPE_PNG
                        );
        $extension = end(explode(".", $basename));

        if(isset($types[$extension]))
            $this->SetType($types[$extension]);

    }
}