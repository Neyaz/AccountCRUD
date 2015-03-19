<?

namespace Core;

/**
 * Конфигурация и профили настройки
 */
class Config
{
    static private $instance = NULL;
    private $config = "";

    /**
     * Синглетон
     * @return Config
     */
    static function Instance()
    {
        if (self::$instance == NULL)
            self::$instance = new Config();
        return self::$instance;
    }
    private function __construct()
    {
        mb_internal_encoding("UTF-8");
        if (!Path::FileExist(Path::Relative('Config.yml'))) throw new LiteWorkInitializationException();
        $this->config = \Symfony\Component\Yaml\Yaml::parse(Path::Relative('Config.yml'));
    }
    private function __clone()
    {
    }

    /**
     * Заливает значения по умолчанию из конфига
     * title, keywords, ...
     */
    public function LoadLayoutDefaultParameters()
    {
        $params = $this->config['default'];
        if ($params != NULL)
            Internal\Runtime::Instance()->InternalGetLayoutData()->SetItems($params);
    }

    public function HaveItem($name)
    {
        $cur = $this->config;
        $arr = explode('/', $name);
        $i=0;
        for(; $i<count($arr); $i++)
        {
            if (isset ($cur[ $arr[$i] ]))
                $cur = $cur[ $arr[$i] ];
            else
                return false;
        }
        return true;
    }

    /**
     * Получить из настроек данные любого типа
     */
    public function GetData($name, $default=null)
    {
        $cur = $this->config;
        $arr = explode('/', $name);
        $i=0;
        for(; $i<count($arr); $i++)
        {
            if (isset ($cur[ $arr[$i] ]))
                $cur = $cur[ $arr[$i] ];
            else
                return $default;
        }
        return $cur;
    }

    /**
     * Получить из настроек строку
     */
    public function GetStr($name, $default=null)
    {
        return strval($this->GetData($name, $default));
    }

    /**
     * Получить из настроек число
     */
    public function GetInt($name, $default=null)
    {
        return intval($this->GetStr($name, $default));
    }

    /**
     * Получить из настроек bool
     */
    public function GetBool($name, $default=null)
    {
        $rr = $this->GetStr($name, $default);
        return Utils::ToBool($rr);
    }

    /**
     * Получить из настроек временной интервал в секундах (цифрой)
     */
    public function GetTimeSpan($name, $default = '10m')
    {
        $val = $this->GetStr($name, $default);
        $time = Utils::TimeSpanStringToSeconds($val);
        if ($time === false)
            return Utils::TimeSpanStringToSeconds($default);
        else
            return $time;
    }

    /**
    * TODO: В новой ветке удалить лишнее
    *
    */
    public function IsDebug()
    {
        return (Config::Instance()->GetStr('mode') == 'dev') || Config::Instance()->GetBool('global/debug');
    }

    public function IsProductionMode()
    {
        return (Config::Instance()->GetStr('mode') == 'prod') || Config::Instance()->GetBool('global/production');
    }
}

?>