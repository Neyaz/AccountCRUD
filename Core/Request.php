<?

namespace Core;

class Request
{
    static private $instance = NULL;

    private $url = "";
    private $urlNoParam = "";
    private $arr = "";

    /**
     * @var Repository\RequestData
     */
    private $data;

    /**
     * @var Repository\FilesData
     */
    private $files;

    /**
     * @var Repository\RequestData
     */
    private $parameters;

    /**
     * Синглтон
     * @return Request
     */
    public static function Instance()
    {
        if (self::$instance == NULL)
        {
            self::$instance = new Request();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->url = $_SERVER['REQUEST_URI'];
        $this->urlNoParam = $this->url;
        if (strpos($this->urlNoParam, '?')!==false)
                $this->urlNoParam = substr($this->urlNoParam, 0, strpos($this->urlNoParam, '?'));

        if ($this->urlNoParam=='' || $this->urlNoParam=='/')
            $this->urlNoParam = 'index';

        $array = explode('/', $this->urlNoParam);

        foreach ($array as $val)
        {
            if ($val != '' && $val != 'index.php')
            {
                $this->arr[] = $val;
            }
        }
        $this->arr[0] = str_replace('index.php', '', $this->arr[0]);
        $this->data = new Repository\RequestData($_POST);
        $this->parameters = new Repository\RequestData($_GET);
        $this->files = new Repository\FilesData($_FILES);
    }

    private function __clone()
    {
    }

    /**
     * Страница запрошена через аякс?
     */
    public function IsAjax()
    {
		if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) return false;
        return $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest';
    }

    /**
     * Данные переданные вместе с запросом (POST)
     * @return \Core\Repository\RequestData
     */
    public function Data()
    {
        return $this->data;
    }
    /**
     * Были ли вместе с запросом переданы данные (POST)
     * @return bool
     */
    public function HaveData()
    {
        return $this->data->HaveItems();
    }

    /**
     * Данные переданные вместе с запросом (GET)<br/>
     * Внимание! Вместо работы с GET рекомендуется передавать необходимые данные через параметры роутинга<br/><br/>
     * <b>Данный метод не рекомендуется к использованию</b>
     * @return \Core\Repository\RequestData
     */
    public function RequestParameters()
    {
        return $this->parameters;
    }
    /**
     * Были ли вместе с запросом переданы данные (GET)<br/>
     * Внимание! Вместо работы с GET рекомендуется передавать необходимые данные через параметры роутинга<br/><br/>
     * <b>Данный метод не рекомендуется к использованию</b>
     * @return bool
     */
    public function HaveRequestParameters()
    {
        return $this->parameters->HaveItems();
    }

    /**
    * Получает доступ к загруженным файлам
    *
    * @return \Core\Repository\FilesData
    */
    public function Files()
    {
        return $this->files;
    }

    /**
    * Были ли переданный файлы при запросе
    * @return bool
    */
    public function HaveFiles()
    {
        return $this->files->HaveItems();
    }

    /**
     * Способ которым был осуществлен текущий запрос<br/>
     * GET, POST, ...
     */
    public function RequestMethod()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Тип запроса (ajax/default)
     * @return string
     */
    public function RequestType()
    {
        if ($this->IsAjax())
            return 'ajax';
        return 'default';
    }

    /**
     * Является ли текущий запрос запросом POST
     */
    public function IsPost()
    {
        return $this->RequestMethod() == 'POST';
    }
    /**
     * Является ли текущий запрос запросом GET
     */
    public function IsGet()
    {
        return $this->RequestMethod() == 'GET';
    }

    /**
     * Имя домена
     * @param \Core\Enum\DomainNameMode $nameMode Что делать с "WWW." в начале имени домена,
     * по умолчанию <b>DomainNameMode::ORIGINAL</b> - не менять
     * @return string
     */
    public function DomainName($nameMode = \Core\Enum\DomainNameMode::ORIGINAL)
    {
        $host = $_SERVER['SERVER_NAME'];
        $fromWWW = strncasecmp($host, 'www.', 4) === 0;

        if ($nameMode == Enum\DomainNameMode::SECOND_LEVEL)
        {
            $pointPos = mb_strrpos($host, '.');
            if ($pointPos === false)
                return $host;
            $pointPos = $pointPos - mb_strlen($host) - 1;
            $pointPos = mb_strrpos($host, '.', $pointPos);
            if ($pointPos === false)
                return $host;
            else
                return mb_substr($host, $pointPos + 1);
        }
        else if ($nameMode == Enum\DomainNameMode::WITHOUT_WWW && $fromWWW)
            $host = Utils::SubstringMid($host, 4);
        else if ($nameMode == Enum\DomainNameMode::FORCE_WWW && !$fromWWW)
            $host = 'www.' . $host;
        return $host;
    }

    /**
     * Адрес ссылающейся страницы
     */
    public function Referer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }

    /**
    * Запрошенный путь
    */
    public function FullPath()
    {
        return $this->url;
    }

    /**
    * Запрошенный URL реквеста полностью, с именем домена, протоколом и полным путем
    */
    public function FullUrl()
    {
        return 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? 's' : '').'://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Возвращает часть пути, начиная с уровня $from и не длинее $count элементов (первый ведущий слеш включается в строку)
     * При $count === NULL будут возвращены все элементы начиная с $from
     */
    public function SubPath($from, $count = NULL)
    {
        if ($count === NULL)
            $end = count($this->arr);
        else
            $end = min($from+$count, count($this->arr));

        $rr = '';
        $i = 0;
        foreach ($this->arr as $val)
        {
            if ($i < $from)
            {
                $i++;
                continue;
            }

            if ($i >= $end)
                break;

            if ($rr!='' || $i == 0) $rr.='/';
            $rr .= $val;
            $i++;
        }
        return $rr;
    }

    /**
     * Возвращает часть пути, которая входит в состав текущего роутинга, включая ведущий слеш
     */
    public function PathInRoute()
    {
        return $this->SubPath(0, LiteWork::Instance()->CurrentRoute()->Level());
    }

    /**
     * Возвращает часть пути, которая начинается сразу после текущего роутинга
     */
    public function PathAfterRoute()
    {
        return $this->SubPath(LiteWork::Instance()->CurrentRoute()->Level());
    }

    /**
     * Получить данные элемента полного пути
     * @param integer $id Индекс элемента начиная с нуля
     * @param string $default Значение которое будет возвращено если запрошенный индекс выходит за границы доступного диапазона
     * @return string
     */
    public function GetAbsoluteItemData($id, $default = '')
    {
        if ($id == 0)
            return '/';
        else if ($id > 0 && $id <= count($this->arr))
            return $this->arr[$id - 1];
        else
            return $default;
    }

    /**
     * Получить строковое значение элемента полного пути
     * @param integer $id Индекс элемента начиная с нуля
     * @param string $default Значение которое будет возвращено если запрошенный индекс выходит за границы доступного диапазона
     * @return string
     */
    public function GetAbsoluteItemStr($id, $default = '')
    {
        $result = $this->GetAbsoluteItemData($id, $default);
        if ($result === $default) return $default;
        return Utils::ToSafeText(Utils::DecodeUrl($result));
    }

    /**
     * Получить числовое значение элемента полного пути
     * @param integer $id Индекс элемента начиная с нуля
     * @param string $default Значение которое будет возвращено если запрошенный индекс выходит за границы доступного диапазона
     * @return string
     */
    public function GetAbsoluteItemInt($id, $default = 0)
    {
        $result = $this->GetAbsoluteItemStr($id, $default);
        if ($result === $default) return $default;
        if (is_numeric($result))
            return intval($result);
        else
            return $default;
    }

    /**
     * Получить булево значение элемента полного пути
     * @param integer $id Индекс элемента начиная с нуля
     * @param string $default Значение которое будет возвращено если запрошенный индекс выходит за границы доступного диапазона
     * @return string
     */
    public function GetAbsoluteItemBool($id, $default = false)
    {
        return Utils::ToBool($this->GetAbsoluteItemStr($id, $default));
    }

    /**
     * Элементы полного пути в виде массива
     */
    public function AbsoluteItemsArray()
    {
        return array_merge(array('/'), $this->arr);
    }

    /**
     * Получить данные элемента запрошенного пути по номеру, считая от роутинга
     * @param integer $id Индекс элемента начиная с нуля
     * @param string $default Значение которое будет возвращено если запрошенный индекс выходит за границы доступного диапазона
     * @return string
     */
    public function GetRelativeItemData($id, $default = NULL)
    {
        $lvl = LiteWork::Instance()->CurrentRoute()->Level();
        $target = $lvl + $id - 1; // Элемент сразу после роутинга
        if ($id == 0)
            return $this->PathInRoute();
        else if ($target >= $lvl && $target < count($this->arr))
            return $this->arr[$target];
        else
            return $default;
    }

    /**
     * Получить строковое значение элемента запрошенного пути по номеру, считая от роутинга
     * @param integer $id Индекс элемента начиная с нуля
     * @param string $default Значение которое будет возвращено если запрошенный индекс выходит за границы доступного диапазона
     * @return string
     */
    public function GetRelativeItemStr($id, $default = '')
    {
        $result = $this->GetRelativeItemData($id, $default);
        if ($result === $default) return $default;
        return Utils::ToSafeText(Utils::DecodeUrl($result));
    }

    /**
     * Получить числовое значение элемента запрошенного пути по номеру, считая от роутинга
     * @param integer $id Индекс элемента начиная с нуля
     * @param string $default Значение которое будет возвращено если запрошенный индекс выходит за границы доступного диапазона
     * @return string
     */
    public function GetRelativeItemInt($id, $default = 0)
    {
        $result = $this->GetRelativeItemData($id, $default);
        if ($result === $default) return $default;
        if (is_numeric($result))
            return intval($result);
        else
            return $default;
    }

    /**
     * Получить булево значение элемента запрошенного пути по номеру, считая от роутинга
     * @param integer $id Индекс элемента начиная с нуля
     * @param string $default Значение которое будет возвращено если запрошенный индекс выходит за границы доступного диапазона
     * @return string
     */
    public function GetRelativeItemBool($id, $default = false)
    {
        return Utils::ToBool($this->GetRelativeItemStr($id, $default));
    }
}