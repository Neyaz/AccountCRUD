<?

namespace Core\Repository;

class FilesData
{
    private $__data;

    public function __construct($arr)
    {
        $this->__data = $this->ArrayConverter($arr);
    }

    /**
     * Есть ли данные в запросе
     * @return bool
     */
    public function HaveItems()
    {
        return count($this->__data) > 0;
    }

    /**
     * Получить данные запроса в виде массива
     * @return string[]
     */
    function ItemsArray()
    {
        return $this->__data;
    }

    /**
     * Задан ли параметр $name
     * @return bool
     */
    public function HaveItem($name)
    {
        return array_key_exists($name, $this->__data);
    }

    /**
     * Получить значение параметра $name в исходном виде, без каких либо преобразований
     * (может быть массивом, строкой и т.п.)
     */
    public function GetItem($name, $default=NULL, $index = 0)
    {
        if ($this->HaveItem($name))
        {
            if(is_array($this->__data[$name]["name"]))
                return $this->__data[$name][$index];
            else
                return $this->__data[$name];
        }
        else
            return $default;
    }

    /**
     * Получить значение параметра А$name виде исходной строки, без преобразований
     * (может содержать потенциально опасные символы, такие как < > " ' и т.п.)
     * @return string
     */
    public function GetErrorCode($name, $default = UPLOAD_ERR_NO_FILE, $index = 0)
    {
        return $this->GetFromField($name, "error", $default, $index);
    }

    /**
     * Получение размера файла
     * @return string
     */
    public function GetSize($name, $default=0, $index = 0)
    {
        return $this->GetFromField($name, "size", $default, $index);
    }

    /**
     * Получение имени файла
     * @return string
     */
    public function GetName($name, $default=0, $index = 0)
    {
        return $this->GetFromField($name, "name", $default, $index);
    }

    /**
     * Получение временного имени файла
     * @return string
     */
    public function GetTmpName($name, $default=0, $index = 0)
    {
        return $this->GetFromField($name, "tmp_name", $default, $index);
    }

    /**
     * Получение типа файла
     * @return string
     */
    public function GetType($name, $default=0, $index = 0)
    {
        return $this->GetFromField($name, "type", $default, $index);
    }

    /**
    * Получить файл в удобном представлении
    *
    * @param string $name
    * @param string $type
    * @param string $index
    * @return \Core\File\File
    */
    public function GetFileObject($name, $type = "file", $index = 0)
    {
        $item = $this->GetItem($name, null, $index);

        if($item == null)
            return null;

        return \Core\File\File::GetFileByType($item, $type);

    }

    private function GetFromField($name, $field, $default, $index)
    {
        $item = $this->GetItem($name, array($field => $default), $index);
        return $item[$field];
    }

    private function ArrayConverter($data, $top = TRUE)
    {
        $files = array();
        foreach($data as $name=>$file)
        {
            if($top)
                $subName = $file['name'];
            else
                $subName = $name;

            if(is_array($subName))
            {
                foreach(array_keys($subName) as $key)
                {
                    $files[$name][$key] = array(
                        'name'     => $file['name'][$key],
                        'type'     => $file['type'][$key],
                        'tmp_name' => $file['tmp_name'][$key],
                        'error'    => $file['error'][$key],
                        'size'     => $file['size'][$key]
                    );
                    $files[$name] = $this->ArrayConverter($files[$name], FALSE);
                }
            }
            else
                $files[$name] = $file;
        }

        return $files;
    }
}

?>
