<?

namespace Core\Repository;

class RequestData
{
    private $__data;

    public function __construct($arr)
    {
        $this->__data = \Core\Utils::StripMagicSlashes($arr);
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
    function ItemsArray($escape = true)
    {
        if($escape)
            return \Core\Utils::ToSafeTextRecursive($this->__data);
        else
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
    public function GetData($name, $default=NULL)
    {
        if ($this->HaveItem($name))
            return $this->__data[$name];
        else
            return $default;
    }
    /**
     * Получить значение параметра А$name виде исходной строки, без преобразований
     * (может содержать потенциально опасные символы, такие как < > " ' и т.п.)
     * @return string
     */
    public function GetBinary($name, $default='')
    {
        return $this->GetData($name, $default);
    }

    /**
     * Получить значение параметра $name в виде декодированного JSON объекта
     * @return string
     */
    public function GetArrayFromJSON($name, $default=null)
    {
        $json = json_decode($this->GetData($name), true);

        if($json === false)
            return $default;
        else
            return $json;

    }

    /**
     * Получить значение строкового параметра $name, сконвертировав специальные
     * символы в безопасные для вывода HTML коды
     * @return string (все потенциально опасные символы заменены на соответствующие коды)
     */
    public function GetStr($name, $default = '')
    {
        return \Core\Utils::ToSafeText($this->GetBinary($name, $default));
    }

    /**
     * Получить значение числового параметра $name
     * @return integer
     */
    public function GetInt($name, $default = 0)
    {
        $val = $this->GetStr($name, $default);
        if (  (string)(int)$val != $val ) return $default;
        return intval($val);
    }

    /**
     * Получить значение числового параметра $name
     * @return integer
     */
    public function GetDouble($name, $default = 0)
    {
        $val = $this->GetStr($name, $default);
        if (!is_numeric($val)) return $default;
        return doubleval($val);
    }

    /**
     * Получить значение булевского параметра $name
     * @return bool
     */
    public function GetBool($name, $default = false)
    {
        return \Core\Utils::ToBool($this->GetStr($name, $default));
    }

    /**
     * Получить значение параметра в виде временной метки $name
     * @return \DateTime
     */
    public function GetDateTime($name, $default = NULL)
    {
        return \Core\Utils::ToDateTime($this->GetStr($name, $default));
    }
}

?>
