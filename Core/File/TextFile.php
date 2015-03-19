<?php

namespace Core\File;
use Core\Path;

class TextFile extends File
{
    private $stream = null;

    /**
    * Записать в файл. Все содержимое будет ПЕРЕЗАПИСАНО! На время записи файл блокируется!
    *
    * @throws \Core\PermissionDeniedException
    * @param string $str - строка для записи в файл
    */
    public function Write($str = "")
    {
        $this->WriteToFileInMode("w", $str);
    }

    /**
    * Дописать в файл. Дописывает полученную строку в конец файла. На время записи файл блокируется!
    *
    * @throws \Core\PermissionDeniedException
    * @param string $str - строка для записи в файл
    */
    public function Append($str = "")
    {
        $this->WriteToFileInMode("a", $str);
    }

    /**
    * Записать в файл в указанном режиме
    *
    * @param string $mode - режим
    * @param string $str - строка для записи в файл
    */
    private function WriteToFileInMode($mode = null, $str = "")
    {
        if($mode != 'a' && $mode != 'w')
            throw new \Exception("Mode Denied");

        $this->stream = fopen($this->GetLocaton(), $mode);
        if ($this->stream)
        {
            flock($this->stream, LOCK_EX);
            fwrite($this->stream, $str);
            flock($this->stream, LOCK_UN);
            fclose($this->stream);
            $this->stream = null;
        }
        else
            throw new \Core\PermissionDeniedException();

    }

    public function ReadFileToString()
    {
        $filePath = $this->GetLocaton();
        $this->stream = fopen($filePath, "r");
        if ($this->stream)
        {
            flock($this->stream, LOCK_EX);
            $contents = fread($this->stream, filesize($filePath));
            flock($this->stream, LOCK_UN);
            fclose($this->stream);
            $this->stream = null;
        }
        else
            throw new \Core\PermissionDeniedException();

        return $contents;
    }
}
?>
