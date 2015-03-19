<?

namespace Core\Repository;

/**
 * Cookie
 * Класс - помошник в работе с куками
 */
class CookiesRepository
{
   static private $instance = NULL;

    private $timeOut;
    private $host;

    private function __construct()
    {
        $this->timeOut = \Core\Config::Instance()->GetTimeSpan('cookies/lifetime', '15d');
        $subDomain = \Core\Config::Instance()->GetBool('cookies/sub_domains', false);
        if ($subDomain)
            $this->host = '.'.  \Core\Request::Instance()->DomainName(\Core\Enum\DomainNameMode::WITHOUT_WWW);
        else
            $this->host = NULL;
    }
    private function __clone()
    {
    }

    /**
     * Устанавливает пользователю Cookie на заданное время (по умолчанию в соответствии с настройкми)
     * @param string $name
     * @param mixed $value
     * @param string|int $lifetime Время существования куки. Если не задано, то используется время из настроек, иначе - берется указанный интервал текстом либо в секундах. Для сохранения кукисы на максимально долгове время можно использовать в качестве значения строку <b>"long"</b>
     * @return bool Успешно ли прошла операция
     */
    public function SetItem($name, $value, $lifetime = NULL, $domain = NULL)
    {
        if ($lifetime === NULL)
            $lifetime = $this->timeOut;
        else if ($lifetime === 'long')
            $lifetime = 220752000;

        if (!is_int($lifetime))
            $lifetime = \Core\Utils::TimeSpanStringToSeconds($lifetime);

        if ($domain === NULL)
            $domain == $this->host;

        $expiration = time() + $lifetime;
        return setcookie($name, $value, $expiration, '/', $domain, false, false);
    }

    /**
     * Удалить Cookie
     * @param string $name Имя
     */
    public function DeleteItem($name)
    {
        return setcookie($name, NULL, 0, '/', $this->host, false, false);
    }

    /**
     * Установлен ли кукис $name
     */
    public function HaveItem($name)
    {
        return array_key_exists($name, $_COOKIE);
    }

    /**
     * Получить элементы в виде массива
     * @return string[]
     */
    function ItemsArray()
    {
        $result = $_COOKIE;
        foreach ($result as $key => $value)
        {
            if (\Core\Utils::SubstringLeft($key, 3) == '___')
                unset ($result[$key]);
        }
        return $result;
    }

    /**
     * Получить значение параметра $name
     * (произвольный тип данных, без преобразований)
     */
    public function GetData($name, $def = NULL)
    {
        if ($this->HaveItem($name))
            return $_COOKIE[$name];
        else
            return $def;
    }

    /**
     * Получить значение строкового параметра $name виде исходной строки, без преобразований
     * (может содержать потенциально опасные символы, такие как < > " ' и т.п.)
     * @return string
     */
    public function GetBinary($name, $default='')
    {
        return strval($this->GetData($name, $default));
    }

    /**
     * Получить значение строкового параметра $name, сконвертировав специальные
     * символы в безопасные для вывода HTML коды
     * @return string (все потенциально опасные символы заменены на соответствующие коды)
     */
    public function GetStr($name, $default='')
    {
        return \Core\Utils::ToSafeText($this->GetData($name, $default));
    }

    /**
     * Получить значение числового параметра $name
     */
    public function GetInt($name, $default=0)
    {
        $val = $this->GetData($name, $default);
        if (!is_numeric($val)) return $default;
        return intval($val);
    }

    /**
     * Получить значение булевского параметра $name
     */
    public function GetBool($name, $default=false)
    {
        return \Core\Utils::ToBool($this->GetData($name, $default));
    }

   /**
     * Внутренний метод для создания
     * @return CookiesRepository
     * @access private
     */
    public static function InternalCreateCookieInstance()
    {
        if (self::$instance == NULL)
        {
            self::$instance = new CookiesRepository();
        }
        return self::$instance;
    }
}

?>
