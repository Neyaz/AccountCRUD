<?

namespace Core;

/**
 * ArrayUtils
 *
 */
class ArrayTools
{
    /**
     * Объединяет несколько массивов в один общий<br/>
     * От стандартной функции отличается тем, что сохраняет все ключи неизменными
     * @return array
     */
    static function Merge()
    {
        $result = array();
        $arrays = func_get_args();
        foreach ($arrays as $arr)
        {
            if(!empty($arr))
            {
                foreach ($arr as $key => $val)
                    $result[$key] = $val;
            }
        }
        return $result;
    }

    /**
     * Объединяет несколько массивов в один общий<br/>
     * От стандартной функции отличается тем, что сохраняет все ключи неизменными
     * @return array
     */
    static function MergeRecursively()
    {
        $result = array();
        $arrays = func_get_args();
        foreach ($arrays as $arr)
        {
            if(!empty($arr))
            {
                foreach ($arr as $key => $val)
                {
                    if(is_array($val) || is_array($result[$key]))
                    {
                        if (!is_array($val))
                            $val = array($val);

                        if(!isset($result[$key]))
                            $result[$key] = array();
                        elseif (!is_array($result[$key]))
                            $result[$key] = array($key);

                        $result[$key] = self::MergeRecursively($result[$key], $val);
                    }
                    else
                        $result[$key] = $val;
                }
            }
        }
        return $result;
    }

    /**
      * @return array
      * @param array $source
      * @param mixed $insert
      * @param int $position
     */
    static function InsertArrayBeforePosition($source, $insert, $position)
    {
        if (!is_array($insert))
            $insert = array($insert);
         return array_merge(array_slice($source,0,$position), $insert, array_slice($source,$position));
     }
    /**
      * @return array
      * @param array $source
      * @param mixed $insert
      * @param int $position
     */
    static function InsertArrayAfterPosition($source, $insert, $position)
    {
        if (!is_array($insert))
            $insert = array($insert);
         return array_merge(array_slice($source,0,$position+1), $insert, array_slice($source,$position+1));
     }

     /**
      * @return array
      * @param array $source
      * @param mixed $insert
      * @param mixed $itemKeyValue
     */
    static function InsertArrayBeforeItem($source, $insert, $itemKeyValue)
    {
        if (!is_array($insert))
            $insert = array($insert);
        $R = array();
        foreach($source as $k=>$v)
        {
            if($k == $itemKeyValue) $R = self::Merge($R, $insert);
            $R[$k] = $v;
        }
        return $R;
     }
    /**
      * @return array
      * @param array $source
      * @param mixed $insert
      * @param int|string $itemKeyValue
     */
    static function InsertArrayAfterItem($source, $insert, $itemKeyValue)
    {
        if (!is_array($insert))
            $insert = array($insert);
        $R = array();
        foreach($source as $k=>$v)
        {
            $R[$k] = $v;
            if($k == $itemKeyValue) $R = self::Merge($R, $insert);
        }
        return $R;
     }

     /**
      * @return array
      * @param array $source
      * @param mixed $insert
     */
    static function InsertFirstItem($source, $key, $val)
    {
        $insert = array($key => $val);
        if (count($source) == 0) return $insert;

        $first = key($source);
        return self::InsertArrayBeforeItem($source, $insert, $first);
     }


    /**
     * Произвести мапинг массива по заданному ключу
     * @param array $array Входной масив
     * @param string $keyName Свойство объектов (либо ключ в подмасиве) которое будет использоваться как ключ для мапинга
     * @param string $additionalKey Свойство у объекта $array[$keyName], которое будет использоваться как ключ для маппинга
     * @return array
     */
    static function MapArray($array, $keyName, $additionalKey = "id")
    {
        $result = array();
        $method = 'Get'.Utils::DashedToCamelCase($keyName);

        foreach ($array as $it)
        {
            if (is_array($it))
                $result[$it[$keyName]] = $it;
            else if (is_object($it))
            {
                $id = call_user_func(array($it, $method));
                if (is_object($id))
                    $id = call_user_func(array($id, 'Get'.Utils::DashedToCamelCase($additionalKey)));
                $result[$id] = $it;
            }
            else
                return FALSE; // Элементы масива должны быть объектами или вложенными масивами
        }

        return $result;
    }

    /**
     * Если пришедший на вход массив - это массив Entity с единственным первичным ключем,
     * то будет произведен мапинг масива по этому ключу.<br/>
     * Иначе пользователь получит эксепшин :'(
     * @param array $array
     * @return array
     */
    static function MapEntityArray($array)
    {
        if (count($array) == 0) return array();

        $it = current($array);
        Internal\Tools::Assert(is_subclass_of($it, 'Core\BaseEntity'), 'Необходимо передать массив состоящий из набора Entity');

        $name = get_class($it);
        $meta = Internal\MetadataManager::Instance()->GetMetadata($name);
        $idName = $meta->getSingleIdentifierFieldName();
        return self::MapArray($array, $idName);
    }

    /**
     * Если пришедший на вход массив - это массив Entity с единственным первичным ключем,
     * то будет произведен мапинг масива по этому ключу.<br/>
     * Иначе будет возвращен исходный массив
     * @param array $array
     * @return array
     */
    static function MapArrayIfPossible($array)
    {
        $it = current($array);
        if(is_object($it))
        {
            $reflect = new \ReflectionClass($it);
            $name = '';
            if ($reflect->implementsInterface('Doctrine\ORM\Proxy\Proxy'))
            {
                $name = $reflect->getParentClass()->getName();
            }
            else if ($reflect->isSubclassOf('Core\BaseEntity'))
                $name = $reflect->getName();

            if ($name != '')
            {
                $meta = Internal\MetadataManager::Instance()->GetMetadata($name);
                if ($meta->isIdentifierComposite)
                    return $array; // Ничего не можем поделать, пользователь сам должен думать о мапинге на множественных первичных ключах
                $idName = $meta->getSingleIdentifierFieldName();
                return \Core\ArrayTools::MapArray($array, $idName);
            }
        }

        return $array;
    }

    /**
     * Преобразовать массив параметров, в строку HTML атрибутов
     * @param string[] $array
     * @return string
     */
    static function ArrayToAttributesString($array = null)
    {
        if ($array == NULL)
            return '';

        $rt = array();
        foreach ($array as $key => $val)
        {
            if($val === null)
                continue;
            if (is_numeric($key))
                $rt[] = $val.'="'.$val.'"';
            else
                $rt[] = $key.'="'.$val.'"';
        }
        return implode(" ", $rt);
    }

    /**
    * Функция для преобразования массива в строку вида $key1=$val1&$key2=$val2
    *
    * @param array $array Массив с данными
    * @param boolean $encode Применять ль перекодировку в URL символы
    * @param boolean $leadQ  Подставлять ли знак вопроса перед строкой
    * @return string
    */
    static function ArrayToGetString($array = null, $encode = true, $leadQ = false)
    {
        if($array == null)
            return '';

        $rt = array();
        foreach ($array as $key => $val)
        {
            if($encode)
            {
                $val = urlencode($val);
            }

            if (is_numeric($key))
                $rt[] = htmlspecialchars($val.'='.$val);
            else
                $rt[] = htmlspecialchars($key.'='.$val);
        }
        $string = implode("&", $rt);
        //if($encode)
        //    $string = urlencode($string);
        if($leadQ)
            $string = '?'.$string;

        return $string;
    }

    static function IsAssociative($arr)
    {
        if (!is_array($arr))
            return false;

        $i = 0;
        foreach ($arr as $key => $value)
        {
            if ($key !== $i++)
                return true;
        }
        return false;
    }

    static function IsVector($arr)
    {
        if (!is_array($arr))
            return false;

        $i = 0;
        foreach ($arr as $key => $value)
        {
            if ($key !== $i++)
                return false;
        }
        return true;
    }
}

?>
