<?php
namespace Core\File;
use Core\Path;

abstract class File
{
    protected $extension = null;
    protected $filename = null;
    protected $basename = null;
    protected $dir = null;
    protected $size = 0;

    protected $upload = false;
    protected $filled = null;

    protected $uploadData = null;

    /**
    * Конструктор
    * Если ничего не передать, создастся пустая заглушка, в пкоторую потом можно добавить файл
    * Если задана строка, то попытаемся получить доступ
    * Если задан массив, думаем, что это файл из $_FILES
    *
    * @param string|array $path
    * @throws FileErrorUploadException, FileNotFoundException
    * @return File
    */
    public function __construct($path = NULL)
    {
        try
        {
            $this->AssignFile($path);
        }
        catch(\Core\NullReferenceException $e) {}
    }



    /**
    * Сброс всех полей на исходное значение
    *
    */
    protected function ResetToDefault()
    {
        $this->basename = null;
        $this->filename = null;
        $this->size = null;
        $this->extension = null;
        $this->dir = null;
        $this->upload = false;
        $this->filled = null;
        $this->uploadData = null;
    }

    /**
    * Проверка верный ли файл нам подсунули
    */
    protected function CheckExtension()
    {
        return true;
    }

    /**
    * Заполняет поля filename, extension, basename по переданной строке
    *
    * @param string $name
    */
    protected function FillByBaseName($name)
    {
        $filenameParts = explode('.', $name);
        $this->SetExtension(end($filenameParts));
        unset($filenameParts[count($filenameParts)-1]);
        $this->SetFileName(implode(".", $filenameParts));
    }


    /**
    * Устанавливает имя файла
    *
    * @param string $name
    */
    protected function SetFileName($name)
    {
        $this->filename = $name;
        $this->UpdateBaseName();
    }

    /**
    * Утанавливает расширение файла
    *
    * @param string $extension
    */
    protected function SetExtension($extension)
    {
        $this->extension = $extension;
        $this->UpdateBaseName();
    }

    /**
    * Обновляет значение поля basename на основе данных из полей filename, extension
    *
    */
    protected function UpdateBaseName()
    {
        $this->basename = $this->filename . "." . $this->extension;
    }


    /**
    * Определяет раздеитель строки (экспериментальная функция!!! )
    *
    * @param string $file - строка с путем
    */
    protected function GetDirectorySeparator($file)
    {

        $delimeter = '/';
        if(mb_strpos($file, $delimeter) === false)
        {
            $delimeter = '\\';
            if(mb_strpos($file, $delimeter) === false)
                $delimeter = DIRECTORY_SEPARATOR;
        }
        return $delimeter;
    }


      /**************************************************/
     /**************** PUBLIC METHODS ******************/
    /**************************************************/

    public static function GetFileByType($data, $type)
    {

        switch($type)
        {
            case "img":
            case "image":
                $object = new ImageFile($data);
                break;
            case "file":
            case "text":
            default:
                $object = new TextFile($data);
                break;
        }

        return $object;
    }


    /**
    * Конструктор
    * Если ничего не передать, создастся пустая заглушка, в пкоторую потом можно добавить файл
    * Если задана строка, то попытаемся получить доступ
    * Если задан массив, думаем, что это файл из $_FILES
    *
    * @param string|array $path
    * @throws NullReferenceException, FileErrorUploadException, FileNotFoundException
    * @return File
    */
    public function AssignFile($path)
    {
        if($path == null)
            throw new \Core\NullReferenceException("File path is not assigned");

        if(is_array($path))
        {
            $this->upload = true;
            $this->uploadData = $path;

            if($this->uploadData["error"] != UPLOAD_ERR_OK)
                throw new \Core\FileUploadErrorException($this->uploadData["error"]);

            $this->FillByBaseName($this->uploadData["name"]);
            if(!$this->CheckExtension())
            {
                $this->ResetToDefault();
                throw new \Core\UnexpectedExtensionException();
            }
            $this->size = $this->uploadData["size"];
            $this->filled = true;

            $this->PostProcessing();

            return $this;
        }

        if(!Path::FileExist($path))
            throw new \Core\FileNotFoundException($path);

        $separator = $this->GetDirectorySeparator($path);
        $pathParts = explode($separator, $path);
        $this->FillByBaseName(end($pathParts));

        if(!$this->CheckExtension())
        {
            $this->ResetToDefault();
            throw new \Core\UnexpectedExtensionException();
        }

        unset($pathParts[count($pathParts)-1]);
        $this->dir = implode($separator, $pathParts);
        $this->size = filesize($path);

        $this->filled = true;

        $this->PostProcessing();

        return $this;
    }

    /**
    * Вызывается после присвоение файла. Используется для заполнения полей раззличных типов файлов
    *
    */
    public function PostProcessing() { }

    public function GetFilename()
    {
        return $this->filename;
    }

    public function GetExtension()
    {
        return $this->extension;
    }

    public function GetBasename()
    {
        return $this->basename;
    }

    public function GetDirectory()
    {
        return $this->dir;
    }

    public function IsReady()
    {
        return (bool)$this->filled;
    }

    public function Remove()
    {
        \Core\Path::RemoveDirectory($this->GetLocaton());
    }

    public function Save($dir = null, $basename = null)
    {
        $oldDir = $this->GetLocaton();

        $this->ApplyNewPath($dir, $basename);

        if($this->upload)
        {
            $destination = Path::Combine($this->GetDirectory(), $this->GetBasename());
            if(!move_uploaded_file($this->uploadData["tmp_name"], $destination))
                throw new \Core\PermissionDeniedException();
            $this->upload = false;
            $this->uploadData = null;
        }
    }

    public function Copy($dir = null, $basename = null)
    {
        $oldDir = $this->GetLocaton();
        $this->ApplyNewPath($dir, $basename);
        copy($oldDir, $this->GetLocaton());
    }

    public function Move($dir = null, $basename = null)
    {
        $oldDir = $this->GetLocaton();
        $this->ApplyNewPath($dir, $basename);
        rename($oldDir, $this->GetLocaton());
    }

    protected function ApplyNewPath($dir = null, $basename = null)
    {
        if($dir != null)
            $this->dir = $dir;

        if($basename != null)
            $this->FillByBaseName($basename);

        if($this->GetBasename() == ".")
            throw new \Core\NameNotAssignedException();

        if(!\Core\Path::FileExist($this->dir))
        {
            if(!mkdir($dir, 0775, true))
                throw new \Core\PermissionDeniedException();
        }
    }

    public function GetLocaton()
    {
        return Path::Combine($this->GetDirectory(), $this->GetBasename());
    }

}
?>
