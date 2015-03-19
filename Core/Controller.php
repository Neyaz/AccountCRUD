<?

namespace Core;

/**
 * Базовый класс для контроллера LiteWork
 */
class Controller
{
    /**
     * Информация о роутинге, который вызвал этот модуль на выполнение
     * @var \Core\Route
     */
    private $route;

    private $currentAction = 'index';

    /**
     * @var \Core\Repository\ResultData
     */
    private $result;

    /**
     * @var \Core\Repository\LayoutData
     */
    private $layoutData;

    /**
     * Определяет какой блок является основным для модуля.
     * В него будет осуществлен вывод
     */
    private $destination = 'content';


    function  __construct()
    {
        $this->layoutData = Internal\Runtime::Instance()->InternalGetLayoutData();
        $this->result = new Repository\ResultData();
    }

    /**
     * Устанавливает модулю текущие параметры роутнга,
     * внутренняя функция, пользователю не вызывать.
     * @access private
     */
    public function SetRoute($route, $applyConfig = true)
    {
        if ($applyConfig)
            $this->ApplyConfig($route->ConfigArray());
        $this->route = $route;
        $this->PrepareRequest();
    }

    /**
     * Устанавливает модулю имя назначения
     * внутренняя функция, пользователю не вызывать.
     */
    public function SetDestination($destination)
    {
        if ($destination == '')
            $destination = 'content';
        $this->destination = $destination;
    }
    /**
     * Получает имя назначения
     * @return string
     */
    public function GetDestination()
    {
        return $this->destination;
    }

    /**
     * Устанавливает модулю экшин,
     * внутренняя функция, пользователю не вызывать.
     * @access private
     */
    public function SetAction($action)
    {
        if ($action == '')
            $action = 'index';
        $this->currentAction = $action;
    }
    /**
     * Получить название запрошенного действия
     * @return string
     */
    public function GetAction()
    {
        return $this->currentAction;
    }

    /**
     * Применить к модулю заданный масив настроек
     * @access private
     */
    public function ApplyConfig($config)
    {
        if ($config == NULL)
            return;

        foreach ($config as $it => $val)
            $this->$it = $val;
    }

    /**
     * Вызывать для создания вложенного View представления.
     * Вызвается только пользователем.
     * @return ViewData
     */
    protected function CreateView($viewName)
    {
        $view = ViewData::Create($this, $viewName);
        return $view;
    }

    /**
     * Возвращает текущий запрос.
     * @return Request
     */
    protected function Request()
    {
        return Request::Instance();
    }

    /**
     * Возвращает данные текущего запроса.<br/>
     * Этот метод сокращение полной формы Request()->Data()<br/>
     * @return \Core\Repository\RequestData
     */
    protected function RequestData()
    {
        return Request::Instance()->Data();
    }

    /**
     * Объект для установки значений возвращаемых модулем
     * @return \Core\Repository\ResultData
     */
    public function Result()
    {
        return $this->result;
    }

    /**
     * Объект для установки значений выводимых напрямую в Layout
     * @return \Core\Repository\LayoutData
     */
    public function LayoutData()
    {
        return $this->layoutData;
    }

    /**
     * Перегружаемая функция - вызывается в самом начале, перед
     * вызовом метода Check (из SetRequest).
     * Перегружать, если нужна дополнительная предварительная обработка
     * входящего запроса.
     * @abstract
     */
    public function PrepareRequest()
    {
    }

    /**
     * <b>Перегружаемая функция</b><br/>
     * Вызывается для проверки может ли модуль
     * обработать текущий запрос.
     * @return bool
     */
    public function Check()
    {
        return true;
    }

    /**
     * <b>Перегружаемая функция</b><br/>
     * Вызывается перед запуском основного метода модуля
     * @abstract
     */
    public function BeforeExecute()
    {
    }

    /**
     * <b>Перегружаемая функция</b><br/>
     * Вызывается после окончания выполнения основного метода модуля
     * @abstract
     */
    public function AfterExecute()
    {
    }

    /**
     * Проверяет имеется ли у модуля метод соответствующий указанному в роутинге
     * @return bool
     * @access private
     */
    public function IsRouteMethodExist()
    {
        $method_exist = method_exists($this, self::GetMethodNameFromAction($this->GetAction()));
        return $method_exist;
    }

    /**
     * Возвращает роутинг, вызвавший модуль
     * @return Route
     */
    public function Route()
    {
        return $this->route;
    }

    public function Application()
    {
        return \Core\LiteWork::Instance()->CurrentApplication();
    }

    /**
     * Название типа модуля
     * @return string
     */
    public function ModuleTypeName()
    {
        return get_class($this);
    }

    public static function GetMethodNameFromAction($action)
    {
        if ($action == '')
            $action = 'OnIndex';
        else
            $action = 'On'.Utils::DashedToCamelCase($action);

        return $action;
    }
}
?>
