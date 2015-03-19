<?

namespace Core;

class User
{
    static private $instance = NULL;
    /**
     * Синглтон
     * @return User
     */
    static function Instance()
    {
        if (self::$instance == NULL)
        {
            $userClass = Config::Instance()->GetStr('user/class', 'User');
            if ($userClass != 'User')
            {
                $path = Path::Relative("Lib", "$userClass.php");
                if (Path::FileExist($path))
                    include_once ($path);
                else
                    \Core\Internal\Tools::Error('Класс User переоппределен, но соответстувующий файл найти не удалось. Сохраните его в папку Lib');

                if (!class_exists($userClass))
                    \Core\Internal\Tools::Error('User переопределен, но соответствующий класс не найден');

                self::$instance = new $userClass();
                if (!is_subclass_of(self::$instance, 'Core\User'))
                    \Core\Internal\Tools::Error('Класс, переопределяющий User, должен наследоваться от него');
            }
            else
            {
                self::$instance = new User();
            }
        }
        return self::$instance;
    }
    private function __construct()
    {
        $this->ConstructSession();
        $this->cookies = Repository\CookiesRepository::InternalCreateCookieInstance();
    }
    private function __clone()
    {
    }

    private $timeOut = 100000000;
    private $namePref = 'sessionParams/';
    private $sessionName = '___user_prefs_data';
    private $cookies = NULL;

    /**
     * Получить контейнер для управления кукисами
     * @return Repository\CookiesRepository
     */
    public function Cookies()
    {
        return $this->cookies;
    }

    /**
     * Получить IP пользователя
     */
    public function IP()
    {
        if (isset($_SERVER["REMOTE_ADDR"]))
            return $_SERVER["REMOTE_ADDR"];

        if (isset($_SERVER["HTTP_X_REAL_IP"]))
            return $_SERVER["HTTP_X_REAL_IP"];

        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
            return $_SERVER["HTTP_X_FORWARDED_FOR"];

        return NULL;
    }

    /**
     * Получить данные запроса в виде массива
     * @return string[]
     */
    function ItemsArray()
    {
        $result = array();
        $p = mb_strlen($this->namePref);
        foreach ($_SESSION as $key => $value)
        {
            if (Utils::SubstringMid($key, $p, 3) != '___')
                $result[Utils::SubstringMid($key, $p)] = $value;
        }
        return $result;
    }

    public function HaveItem($name)
    {
        return array_key_exists($this->namePref.$name, $_SESSION);
    }

    /**
     * Записать в пользовательскую сессию данные любого типа
     */
    public function SetItem($name, $val)
    {
        $_SESSION[$this->namePref.$name] = $val;
    }

    /**
     * Удалить значение из сесси по имени
     */
    public function DeleteItem($name)
    {
        unset($_SESSION[$this->namePref.$name]);
    }

    /**
     * Получить из пользовательской сессии данные любого типа
     */
    public function GetData($name, $default=NULL)
    {
        if ($this->HaveItem($name))
            return $_SESSION[$this->namePref.$name];
        else
            return $default;
    }

    /**
     * Получить из пользовательской сессии строку
     */
    public function GetStr($name, $default='')
    {
        if ($this->HaveItem($name))
            return Utils::ToSafeText($_SESSION[$this->namePref.$name]);
        else
            return strval($default);
    }

    /**
     * Получить из пользовательской сессии число
     */
    public function GetInt($name, $default=0)
    {
        $val = $this->GetStr($name, $default);
        if (!is_numeric($val)) return $default;
        return intval($val);
    }

    /**
     * Получить из пользовательской сессии булевое значение
     */
    public function GetBool($name, $default=false)
    {
        $val = $this->GetStr($name, $default);
        return Utils::ToBool($val);
    }

    /**
     * Инициализировать пользователя
     */
    private function ConstructSession()
    {
        $previous_name = session_name($this->sessionName);
        $this->timeOut = Config::Instance()->GetTimeSpan('user/lifetime', '1h');
        ini_set('session.gc_maxlifetime', $this->timeOut);
        ini_set('session.cookie_lifetime', $this->timeOut);

        $host = Request::Instance()->DomainName(\Core\Enum\DomainNameMode::WITHOUT_WWW);

        $subDomain = Config::Instance()->GetBool('user/sub_domains', false);
        if ($subDomain)
            session_set_cookie_params($this->timeOut, '/', '.'.$host);
        else
            session_set_cookie_params($this->timeOut);

        $fixIp = Config::Instance()->GetBool('user/fix_ip', true);
        session_start();

        if ($this->GetStr('IP', '') == '')
            $this->SetItem('IP', $this->IP());

        if ($this->GetStr('role', '') == '')
            $this->SetItem('role', 'unauthorized');

        if ($this->GetStr('locale', '') == '')
            $this->SetItem('locale', \Core\Config::Instance()->GetStr('default/locale'));

        if ($fixIp && $this->GetStr('IP')!=$this->IP())
        {
            $this->Destroy();
        }
    }

    /**
     * Закрыть сессию
     */
    public function Destroy()
    {
        session_destroy();
        reset($_SESSION);
        $this->ConstructSession();
    }

    /**
     * Задать роль пользователя
     * @param string $role
     */
    public function SetRole($role)
    {
        $this->SetItem('role', $role);
    }

    /**
     * Получить текущую роль пользователя
     * Значение по умолчанию - unauthorized
     */
    public function GetRole()
    {
        return $this->GetStr('role', 'unauthorized');
    }

    /**
     * Задать последний посещенный путь. Удобно использовать для возврата пользователя
     * @param string $path Путь. Необязательный аргумент, по умолчанию - текущий путь
     */
    public function SetLastPath($path = NULL)
    {
        if ($path == NULL) $path = Request::Instance()->FullPath();
        $this->SetItem('last_path', $path);
    }

    /**
     * Получить последний сохраненный путь
     */
    public function GetLastPath()
    {
        return $this->GetStr('last_path', '/');
    }

    /**
    * Установить локаль для пользователя
    *
    * @param string $locale Локаль
    */
    public function SetLocale($locale = 'ru_RU')
    {
        $this->SetItem('locale', $locale);
    }
    /**
    * Получить установленную локаль пользователя
    */
    public function GetLocale()
    {
        $delault = \Core\Config::Instance()->GetStr('default/locale', 'ru_RU');
        return $this->GetStr('locale', $delault);
    }
}

?>