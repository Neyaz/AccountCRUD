<?

namespace Core;

/**
 * Path
 */
class Path
{
    /**
     * Сформировать из фрагментов пути полный путь
     * @param string[] $name,... Фрагменты формируемого пути через запятую
     */
    public static function Combine()
    {
        $path = '';
        $arguments = func_get_args();
        $args = array();
        foreach($arguments as $a)
        {
            if (is_array($a))
                foreach ($a as $sub)
                    $args[] = $sub;
            else
                if($a !== '') // Удаляем пустые элементы
                    $args[] = $a;
        }

        $arg_count = count($args);
        for($i=0; $i<$arg_count; $i++)
        {
            $folder = $args[$i];

            if($i != 0 && $folder[0] == DIRECTORY_SEPARATOR) $folder = substr($folder,1);
            if($i != $arg_count-1 && substr($folder,-1) == DIRECTORY_SEPARATOR) $folder = substr($folder,0,-1);

            $path .= $folder;
            if($i != $arg_count-1) $path .= DIRECTORY_SEPARATOR; //Add the '/' if its not the last element.
        }
        return $path;
    }

    /**
     * Путь до корня текущего проекта.
     * В этой папке находится все файлы сайта
     */
    static function BasePath()
    {
        return realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
    }

    /**
    *  Вычисление виртуального пути из базового
    *  или указанного пользователем
    */

    static function VirtualPath($path)
    {
        if (empty($path))
            $path = self::BasePath();

        return str_replace('\\', '/', str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', $path));
    }

    /**
     * Получить подпуть отбросив часть совпадающую с заданным родительским путем
     * @param type $originalPath
     * @param type $parentPath
     * @return type
     */
    public static function ExtractRelativeSubPath($originalPath, $parentPath)
    {
        $cur = explode(DIRECTORY_SEPARATOR, $originalPath);
        $par = explode(DIRECTORY_SEPARATOR, $parentPath);
        $result = '';

        for ($i = 0; $i < count($cur); $i++)
        {
            if ($i < count($par) && $par[$i] == $cur[$i])
                continue;
            if ($result != '')
                $result .= DIRECTORY_SEPARATOR;
            $result .= $cur[$i];
        }
        return $result;
    }

    /**
     * Сформировать из фрагментов пути полный путь, начиная от корневой папки - Path::BasePath()
     * @param string $name,... Фрагменты формируемого пути через запятую
     */
    public static function Relative()
    {
        $args = func_get_args();
        return Path::Combine(Path::BasePath(), $args);
    }

    /**
     * Рекурсивно удалить путь
     * @param string $path
     */
    public static function RemoveDirectory($path)
    {
       if (is_dir($path))
       {
         $files = scandir($path);
         foreach ($files as $file)
             if ($file != "." && $file != "..")
                 Path::RemoveDirectory(Path::Combine($path, $file));
         rmdir($path);
       }
       else if (file_exists($path))
           unlink($path);
     }

    /**
     * Рекурсивно копировать путь
     * @param type $path
     */
    public static function CopyDirectory($src, $dst)
    {
       if (is_dir($src))
       {
         if (!is_dir($dst))
             mkdir($dst);
         $files = scandir($src);
         foreach ($files as $file)
            if ($file != "." && $file != "..")
                Path::CopyDirectory(Path::Combine($src, $file), Path::Combine($dst, $file));
       }
       else if (file_exists($src))
           copy($src, $dst);
     }

    /**
     * Регистрозависимая проверка наличия файла
     */
    public static function FileExist($file)
    {
        if (!file_exists($file)) return false;
        if (realpath($file) != $file) return false;
        return true;
    }

    /**
    * Рекурсивно установить права
    *
    * @param string $path путь к папке
    * @param int $filePerm права на файлы
    * @param int $dirPerm права на папку
    */
    static function RecursiveChmod($path, $filePerm=0644, $dirPerm=0755)
    {
        if(!file_exists($path))
            return false;

        if(is_dir($path))
        {
            $foldersAndFiles = scandir($path);
            $entries = array_slice($foldersAndFiles, 2);
            foreach($entries as $entry)
                self::RecursiveChmod($path."/".$entry, $filePerm, $dirPerm);

            chmod($path, $dirPerm);
        }
        elseif(is_file($path))
            chmod($path, $filePerm);

        return true;
    }

    /**
    * Получает список записей в директории (папки+файлы)
    *
    * @param string $path Дериктория
    */
    static function GetEntriesInFolder($path)
    {
        $data = array();
        $directory = dir($path);
        if($directory == false || $directory == null)
            return $data;

        while (false !== ($entry = $directory->read())) {
            if($entry == '.' || $entry == '..') continue;
            $data[] = $entry;
        }

        return $data;
    }

    /**
    * Получает список записей в директории (файлы)
    *
    * @param string $path Дериктория
    */
    static function GetFilesInFolder($path)
    {
        if(!self::FileExist($path))
            return array();
        $data = array();
        $directory = dir($path);
        if($directory == false || $directory == null)
            return $data;

        while (false !== ($entry = $directory->read())) {
            if($entry == '.' || $entry == '..' || is_dir(self::Combine($path, $entry))) continue;
            $data[] = $entry;
        }

        return $data;
    }

    /**
    * Получает список записей в директории (дирктории)
    *
    * @param string $path Дериктория
    */
    static function GetSubfoldersInFolder($path)
    {
        $data = array();
        $directory = dir($path);
        if($directory == false || $directory == null)
            return $data;

        while (false !== ($entry = $directory->read())) {
            if($entry == '.' || $entry == '..' || !is_dir(self::Combine($path, $entry))) continue;
            $data[] = $entry;
        }

        return $data;
    }

    public static function Basename($file)
    {
        $delimeter = DIRECTORY_SEPARATOR;
        if(mb_strpos($file, $delimeter) === false)
        {
            $delimeter = '/';
            if(mb_strpos($file, $delimeter) === false)
            {
                $delimeter = '\\';
                if(mb_strpos($file, $delimeter) === false)
                    $delimeter = DIRECTORY_SEPARATOR;
            }
        }

        while(Utils::SubstringRight($file, 1) == $delimeter)
            $file = Utils::SubstringLeft($file, mb_strlen($file)-1);
        return end(explode($delimeter, $file));
    }

    public static function FormatFileSize($file)
    {
        $bytes = filesize($file);
        if ($bytes >= 1024*1024*1024)
            return round($bytes / 1024*1024*1024, 2) . ' GB';
        if ($bytes >= 1024*1024)
            return round($bytes / 1024*1024, 2) . ' MB';
        return round($bytes / 1024, 2) . ' KB';
    }
}

?>
