<?

namespace Core;

/**
 * Application
 * Класс приложения
 */
class Application
{
    /**
     * @var Controller[]
     */
    private $submodules = array();

    /**
     * @var \Core\Repository\LayoutData
     */
    private $layoutData;

    function __construct()
    {
        $this->layoutData = Internal\Runtime::Instance()->InternalGetLayoutData();
    }

    /**
     * <b>Перегружаемая функция</b><br/>
     * Вызывается для проверки может ли модуль
     * обработать текущий запрос.
     * @return bool
     */
    function Check()
    {
        return true;
    }

    /**
     * <b>Перегружаемая функция</b><br/>
     * Вызывается перед запуском основного метода модуля
     * @abstract
     */
    function BeforeExecute()
    {
    }

    /**
     * <b>Перегружаемая функция</b><br/>
     * Вызывается после окончания выполнения основного метода модуля
     * @abstract
     */
    function AfterExecute()
    {
    }

    static function GetApplicationClassName($appName)
    {
        if ($appName == 'RootMode') return 'Core\RootModeApplication';
        return $appName . 'Application';
    }

    /**
     * Создать подмодуль по имени
     * @param string $name Имя модуля с полным неймспейсом
     * @param string $destination Основное имя назначения для модуля
     * @param string $action Вызываемое действие
     * @return Controller
     */
    public function CreateSubModule($name, $destination, $action = 'index')
    {
        $module = \Core\Internal\ClassFactory::CreateModule($name, $action);
        $module->SetDestination($destination);
        $this->submodules[] = $module;
        return $module;
    }

    /**
     * Оменить выполнение подмодуля
     * @param Controller $subModule
     */
    public function ExcludeSubModule($subModule)
    {
        foreach ($this->submodules as $key => $value)
        {
            if ($value == $subModule)
                unset ($this->submodules[$key]);
        }
    }

    public function SubModulesArray()
    {
        return $this->submodules;
    }

    public function LayoutData()
    {
        return $this->layoutData;
    }
}

?>
