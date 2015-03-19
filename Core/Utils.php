<?

namespace Core;

/**
 * Преобразование величин и форматов,
 * дополнительные инструменты
 */
class Utils
{
    /**
     * Удаляет двойные слешы, создаваемые magic_quotes.
     * Умеет рекурсивно обрабатывать масивы.
     * Двойные слешы будут удалены только, если get_magic_quotes_gpc() == true
     * @param data mixed
     * @return mixed
     */
    static function StripMagicSlashes($data)
    {
        if (is_array($data))
        {
            $rv = array();
            foreach ($data as $key => $val)
            {
                $rv[$key] = self::StripMagicSlashes($val);
            }
            return $rv;
        }
        else if (!is_object($data))
        {
            if (get_magic_quotes_gpc())
                return stripslashes($data);
            else
                return $data;
        }
        else
            \Core\Internal\Tools::Error('Функция StripMagicSlashes не может принимать объект в качестве аргумента');
    }

    static function DecodeUrl($str)
    {
        return urldecode(strval($str));
    }

    /**
     * Превращает строку содержащую любые символы, в безопасный для вывода в браузер текст.
     * & (амперсанд) преобразуется в '&amp;'
     * " (двойная кавычка) преобразуется в '&quot;'
     * ' (одиночная кавычка) преобразуется в '&#039;'
     * < (знак "меньше чем") преобразуется в '&lt;'
     * > (знак "больше чем") преобразуется в '&gt;'
     * @return string
     */
    static function ToSafeText($str)
    {
        return htmlspecialchars(strval($str), ENT_QUOTES, "UTF-8");
    }

    /**
     * Рекурсивно пременяет ToSafeText
     * @return array
     */
    static function ToSafeTextRecursive($data)
    {
        $response = array();
        foreach($data as $key => $item)
        {
            if(is_array($item))
                $response[$key] = self::ToSafeTextRecursive($item);
            else
                $response[$key] = self::ToSafeText($item);
        }
        return $response;
    }

    /**
     * Преобразовать строку к типу bool
     * @return bool
     */
    static function ToBool($val)
    {
        $val = (string)$val;
        return $val=='1' || $val=='on' || $val=='true' || $val=='enabled' || $val=='checked';
    }

    /**
     * Преобразовать в JSON
     * @param mixed $data
     * @return string
     */
    static function ToJSON($data)
    {
        $flags = JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_TAG;

        $data = self::ExpandObjects($data, 180, 'JSON'); // было 9

        return json_encode($data, $flags);
    }

    /**
     * Преобразовать произвольные данные до заданного уровня вложенности к простому представлению.
     * Объекты будут преобразованы в массивы.
     * @param type $data
     * @param type $resultPresentation
     * @return type
     */
    static function ExpandObjects($data, $level, $resultPresentation = NULL)
    {
        $result = array();
        $map = array();
        Internal\Tools::ExpandObjectsInternal($result, $data, $map, $level, $resultPresentation);
        return $result;
    }

    /**
     * Реализация ucfirst для работы с многобайтными строками
     * @param string $string
     * @return string
     */
    static function Ucfirst($string)
    {
        return mb_strtoupper(self::SubstringLeft($string, 1)).self::SubstringMid($string, 1);
    }

    /**
     * Рекурсивный trim
     * @param mixed $data
     * @return mixed
     */
    static function Trim($data, $charlist = " \t\n\r\0\x0B")
    {
        if (is_array($data))
        {
            foreach ($data as $key => $value)
            {
                $data[$key] = self::Trim($value, $charlist);
            }
            return $data;
        }
        return trim($data, $charlist);
    }

    /**
     * возвращает подстроку с 0 по $count из строки $string
     * @param string $string
     * @param integer $count
     * @return string
     */
    static function SubstringLeft($string, $count)
    {
        $vleft = mb_substr($string, 0, $count);
        return $vleft;
    }

    /**
     * возвращает подстроку с $from до $count (если $count === NULL, до последнего символа) из строки $string
     * @param string $string
     * @param integer $from
     * @param integer $count
     * @return string
     */
    static function SubstringMid($string, $from, $count = null)
    {
        if ($count !== NULL)
            $val = mb_substr($string, $from, $count);
        else
            $val = mb_substr($string, $from);
        return $val;
    }

    /**
     * возвращает подстроку с $count до последнего символа из строки $string
     * @param string $string
     * @param integer $count
     * @return string
     */
    static function SubstringRight($string, $count)
    {
        $vright = mb_substr($string, mb_strlen($string)-$count, $count);
        return $vright;
    }

    static function CompareStrings($string1, $string2, $caseSensetive = true)
    {
        if ($caseSensetive)
            return strcmp($string1, $string2);

	    $string1 = mb_strtolower($string1);
	    $string2 = mb_strtolower($string2);
	    return strcmp($string1, $string2);
	}

    /**
     * Преобразовать строку в временной интервал в секундах
     * @return int Временной интервал в секундах
     */
    static function TimeSpanStringToSeconds($str)
    {
        if ($str == NULL || strlen($str) == 0)
            return false;

        // Если число задано цифрой, то считаем в секундах
        if (is_numeric($str))
            return intval($str);

        $type = substr($str, strlen($str)-1);
        $data = substr($str, 0, strlen($str)-1);

        if (!is_numeric($data))
            return false;
        $data = intval($data);

        switch ($type)
        {
            case 's':
                $val = $data;
                break;
            case 'm':
                $val = $data * 60;
                break;
            case 'h':
                $val = $data * 60 * 60;
                break;
            case 'd':
                $val = $data * 60 * 60 * 24;
                break;
            default:
                return false;
        }
        return $val;
    }

    /**
     * Преобразовать слово из варианта с тире и подчеркиваниями в СловаСБольшойБуквы<br/>
     * Например: 'table_name' -> 'TableName'
     * @param string $word
     * @param boolean $firstUpper
     * @return string
     */
    public static function DashedToCamelCase($word, $firstUpper = true)
    {
        $rt = str_replace(" ", "", ucwords(strtr($word, "_-", "  ")));
        if (!$firstUpper) $rt = lcfirst($rt);
        return  $rt;
    }

    /**
     * Проверка является ли URL абсолютным
     * @param string $url
     * @return bool
     */
    public static function IsAbsoluteUrl($url)
    {
        if (strpos($url, '//') !== false) return true;
        if (\Core\Utils::SubstringLeft($url, 1) == '/') return true;
        return false;
    }

    /**
    * Генерирует пароль
    *
    * @param int $length Длина пароля
    * @param bool $numeric Состоит ли из цифр
    * @param bool $letters Состоит ли из букв
    * @param bool $symbols Состоит ли из знаков
    *
    * @return string
    */
    public static function GeneratePassword ($length = 8, $numeric = true, $letters = true, $symbols = false)
    {
        $password = "";

        $possible = "";
        if($numeric)
            $possible.= "2346789";
        if($letters)
            $possible.="abcdefghjkmnpqrtuvwxyz";
        if($symbols)
            $possible.="@#$%^&*-+=";

        $maxlength = strlen($possible);
        $maxspec = $length / 3;

        $i = 0;
        $lastSpec = false;
        $totalspec = 0;
        while ($i < $length)
        {
            $char = substr($possible, mt_rand(0, $maxlength-1), 1);
            $spec = ($char < 'a' || $char > 'z') && ($char < 'A' || $char > 'Z') && ($char < '0' || $char > '9');
            if (($i == 0 || $i == $length - 1) && $spec)
                continue; // По краям буквы
            if ($lastSpec && $spec)
                continue; // Не ставим два спецсимвола подряд
            if ($totalspec >= $maxspec && $spec)
                continue; // Ограничем максимальное количество спецсимволов
            $password .= $char;
            $i++;
            $lastSpec = $spec;
            if ($spec) $totalspec++;
        }

        return $password;
    }

    public static function MakePasswordHash($password, $salt = NULL)
    {
        if ($salt === NULL)
            $salt = Config::Instance()->GetStr('user/salt', '!@');

        $hash = strtoupper(md5($salt . md5($password)));
        return $hash;
    }

    /**
     * Конвертировать строку в объект DateTime
     * @param string|DateTime $value
     * @return \DateTime
     */
    public static function ToDateTime($value)
    {
        if (is_a($value, 'DateTime'))
            return $value;

        $stamp = strtotime($value);
        $obj = new \DateTime();
        $obj->setTimestamp($stamp);

        return $obj;
    }

    /**
    * Проверяет на валидность дату.
    * Допустимые форматы:
    *    * dd.mm.yyyy    # Russia
    *    * dd-mm-yyyy
    *    * mm/dd/yyyy    # USA
    *    * yyyy-mm-dd    # iso 8601
    *
    * @param string $date
    * @return boolean Существует ли введенная дата
    */
    public static function DateCheck($date)
    {
        if(strlen($date) == 10)
        {
            $pattern = '/\.|\/|-/i';
            preg_match($pattern, $date, $char);


            $array = preg_split($pattern, $date, -1, PREG_SPLIT_NO_EMPTY);

            if(strlen($array[2]) == 4)
            {
                // dd.mm.yyyy || dd-mm-yyyy
                if($char[0] == "."|| $char[0] == "-")
                {
                    $month = $array[1];
                    $day = $array[0];
                    $year = $array[2];
                }
                // mm/dd/yyyy    # Для даты формата США
                if($char[0] == "/")
                {
                    $month = $array[0];
                    $day = $array[1];
                    $year = $array[2];
                }
            }
            // yyyy-mm-dd    # iso 8601
            if(strlen($array[0]) == 4 && $char[0] == "-")
            {
                $month = $array[1];
                $day = $array[2];
                $year = $array[0];
            }

            if(checkdate($month, $day, $year))
                return TRUE;
            else
                return FALSE;
        }
        else
            return FALSE;
    }
}

?>
