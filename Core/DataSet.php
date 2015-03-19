<?

namespace Core;

/**
 * DataSet
 * Набор данных для работы с информацией пришедшей в запросе и
 * вывода её в поля объектов базы данных
 */
class DataSet
{
    /**
     * Создать набор данных на основе запроса по заданным именам.
     * @example $this->CreateFromRequest('name:string', 'enabled:bool', 'size:int', 'description:original');
     * @return DataSet
     */
    public static function CreateFromRequest()
    {
        $result = new DataSet();
        $params = func_get_args();
        $ok = call_user_func_array(array($result, 'LoadFromRequest'), $params);
        if ($ok)
            return $result;
        else
            return NULL;
    }

    /**
     * Загрузить все данные из XML объекта
     * @param SimpleXMLElement $xml
     * @param bool $overwrite Перетирать ли уже имеющиеся данные
     */
    public static function CreateFromXmlObject($xml)
    {
        $result = new DataSet();
        $result->LoadFromXmlObject($xml);
        return $result;
    }

    /**
     * Установить набор элементов массивом. Масив должен быть представлен в виде ключ-значение.
     * Перед установкой старые элементы не сбрасываются.
     * @param string[] $data
     * @return ResultData Возвращает $this для возможности объявления цепочек вызовов
     */
    public function SetItems($data)
    {
        if ($data == NULL) return;

        foreach ($data as $key => $val)
        {
            $this->$key = $val;
        }
        return $this;
    }

    /**
     * Получить все элементы массивом. Масив будет представлен в виде ключ-значение.
     * @return string[]
     */
    public function GetItems()
    {
        $reflect = new \ReflectionClass($this);
        $vars = get_object_vars($this);
        foreach ($vars as $key => $val)
            if ($reflect->hasProperty($key))
                unset ($vars[$key]);

        return $vars;
    }

    /**
     * Загрузить указанные данные из запроса по их именам в текущий набор.
     * После вызова этой функции все загруженные данные доступны как $dataset->varName
     * @example $this->LoadRequestData('name:string', 'enabled:bool', 'size:int', 'description:binary');
     * @return bool
     */
    protected function LoadFromRequest()
    {
        $arguments = func_get_args();
        $args = array();
        foreach($arguments as $a) if($a !== '') $args[] = $a;

        foreach ($args as $val)
        {
            $required = true;
            $data = explode('=', $val);
            $main = trim($data[0]);
            if (count($data) > 1)
            {
                $def = trim($data[1]);
                $required = false;
            }
            else
                $def = NULL;

            $arr = explode(':', $main);
            if (count($arr) != 2 && count($arr) != 1) \Core\Internal\Tools::Error("LoadRequestData - неверное значение у аргумента: $val");

            $varName = trim($arr[0]);
            if (count($arr) > 1)
                $tp = trim($arr[1]);
            else
                $tp = 'string';

            if ($tp == 'bool') $required = false; // Значения bool могут быть пропущены если они == false

            if ($required && !Request::Instance()->Data()->HaveItem($varName))
            {
                DebugPrint("Параметр запроса $varName не найден", "WARNING");
                return false;
            }

            if (!$this->AddFromRequest($varName, $tp, $def))
                \Core\Internal\Tools::Error ("LoadRequestData - неверный тип данных у аргумента: $val");
        }
        return true;
    }

    /**
     * Добавить одну переменную из данных запроса по имени в текущий набор
     * @param string $varName
     * @param string $varType
     * @param type $defaultValue
     * @return bool Удачно ли прошло добавление
     */
    public function AddFromRequest($varName, $varType='string', $defaultValue=NULL)
    {
        switch ($varType)
        {
         case 'bool':
            $this->$varName = Request::Instance()->Data()->GetBool($varName, $defaultValue);
            break;
         case 'int':
         case 'integer':
            $this->$varName = Request::Instance()->Data()->GetInt($varName, $defaultValue);
            break;
         case 'double':
            $this->$varName = Request::Instance()->Data()->GetDouble($varName, $defaultValue);
            break;
         case 'string':
            $this->$varName = Request::Instance()->Data()->GetStr($varName, $defaultValue);
            break;
         case 'binary':
            $this->$varName = Request::Instance()->Data()->GetBinary($varName, $defaultValue);
            break;
        case 'datetime':
        case 'date':
        case 'time':
            $this->$varName = Request::Instance()->Data()->GetDateTime($varName, $defaultValue);
            break;

         default:
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Загрузить все данные из XML объекта
     * @param SimpleXMLElement $xml
     * @param bool $overwrite Перетирать ли уже имеющиеся данные
     * @param bool $lcfirts Преобразовывать ли первую букву поля на строчную(VarName->varName)
     */
    public function LoadFromXmlObject($xml, $overwrite = false, $lcfirts = false)
    {
        $local = get_object_vars($this);
        foreach ($xml->attributes() as $key => $val)
        {
            $exist = array_key_exists($key, $local);
            if (!$exist || $overwrite)
            {
                if($lcfirts)
                    $key = lcfirst($key);
                $this->$key = (string)$val;
            }
        }
    }

    /**
     * Получить поле по нечеткому имени.
     * Допустимы варианты написания слитно и через подчеркивание, в любом регистре.
     * @param string $name
     * @return string
     */
    function GetSimilarPropertyName($name)
    {
        $local = $this->GetItems();

        if (array_key_exists($name, $local))
            return $name;

        $solidName = str_replace('_', '', $name);

        foreach ($local as $key => $value)
        {
            $solidKey = str_replace('_', '', $key);

            if (Utils::CompareStrings($key, $name, false) == 0)
                return $key;
            if (Utils::CompareStrings($key, $solidName, false) == 0)
                return $key;
            if (Utils::CompareStrings($solidKey, $solidName, false) == 0)
                return $key;
        }

        return false;
    }

    /**
     * Изменить имя поля
     * @param string $fieldName
     * @param string $newFieldName
     */
    function RenameItem($fieldName, $newFieldName)
    {
        $this->$newFieldName = $this->$fieldName;
        unset($this->$fieldName);
    }

    /**
     * Копировать поле
     * @param string $fieldName
     * @param string $newFieldName
     */
    function CopyItem($fieldName, $newFieldName)
    {
        $this->$newFieldName = $this->$fieldName;
    }
    
    /**
     * Выгрузить данные в запись БД
     * @param BaseEntity $target Объект базы данных для записи
     */
    function ApplyToEntity($target)
    {
        $reflection = new \ReflectionClass($target);
        $name = Internal\MetadataManager::Instance()->GetEntityName($reflection);
        $data = Internal\MetadataManager::Instance()->GetFields($name);

        foreach ($data as $it)
        {
            $propertyName = $it['fieldName'];
            $dataItemName = $this->GetSimilarPropertyName($propertyName);

            if ($dataItemName !== false)
            {
                $method = 'Set' . \Core\Utils::DashedToCamelCase($propertyName);
                $type = mb_strtolower($it['type']);
                if ($reflection->hasMethod($method))
                {
                    $methodCall = $reflection->getMethod($method);
                    $value = $this->$dataItemName;

                    switch ($type)
                    {
                        case 'boolean':
                            if (mb_strtolower($value) == 'false' || $value=='0' || $value == '')
                                $value = false;
                            else
                                $value = true;
                            break;
                        case 'integer':
                            $value = intval($value);
                            break;
                        case 'string':
                            $value = strval($value);
                            break;
                        case 'datetime':
                        case 'date':
                        case 'time':
                            $value = Utils::ToDateTime($value);
                            break;
                    }

                    $args = array($value);
                    $methodCall->invokeArgs($target, $args);
                }
            }
        }
    }
}

?>