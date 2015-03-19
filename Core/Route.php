<?

namespace Core;

class Route
{
    var $options;
    var $pathItems;
    var $level;
    var $incSubPath = false;

    public function __construct($path, $options)
    {
        $this->options = $options;
        $this->pathItems = explode('/', $path);

        foreach ($this->pathItems as $key => $value)
        {
            if ($value == '*')
            {
                unset($this->pathItems[$key]);
                $this->incSubPath = true;
            }
        }

        $this->level = count($this->pathItems);
        $this->options = $this->ExpandInfo($this->options);
    }

    private function ExpandInfo($arr)
    {
        foreach ($arr as $id => $val) // Замена волшебных %X
        {
            if (is_string($val))
            {
                if (substr($val, 0, 1)=='%')
                {
                    $a = intval($this->level);
                    $b = intval(substr($val, 1));
                    $val = Request::Instance()->GetAbsoluteItemData($a + $b);
                    if ($val != '')
                        $arr[$id] = $val;
                    else
                        $arr[$id] = NULL;
                }
            }
            else if (is_array($val))
                $arr[$id] = $this->ExpandInfo($val);
        }
        return $arr;
    }

    /**
     * Получить параметр роутинга по имени
     * @return mixed
     */
    public function GetData($name, $def=NULL)
    {
        if ($this->HaveItem($name))
            return $this->options[$name];
        else
            return $def;
    }

    /**
     * Получить строковый параметр роутинга по имени
     * @return string
     */
    public function GetStr($name, $def='')
    {
        return strval($this->GetData($name, $def));
    }

    /**
     * Получить числовой параметр роутинга по имени
     * @return integer
     */
    public function GetInt($name, $def=0)
    {
        $val = $this->GetStr($name, $def);
        if (!is_numeric($val)) return $def;
        return intval($val);
    }

    /**
     * Получить булевский параметр роутинга по имени
     * @return bool
     */
    public function GetBool($name, $def=false)
    {
        return Utils::ToBool($this->GetData($name, $def));
    }

    /**
     * Проверка наличия парметра в роутинге по имени
     * @return bool
     */
    public function HaveItem($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * Получить все содержимое роутинга в виде массива
     * @return string[]
     */
    function ItemsArray()
    {
        return $this->options;
    }

    /**
     * Массив элементов пути роутинга
     * @return array
     */
    public function RoutePathArray()
    {
        return array_merge(array('/'), $this->pathItems);
    }

    /**
     * Включает ли роутинг подпути (true - если он оканчивается звездочкой)
     * @return bool
     */
    public function IsIncludingSubPath()
    {
        return $this->incSubPath;
    }

    /**
     * Глубина вложеннности роутинга
     * @return integer
     */
    public function Level()
    {
        return $this->level;
    }

    /**
     * Получить параметры настроек роутинга, для применения к модулю
     * @return array
     */
    public function ConfigArray()
    {
        if ($this->HaveItem('config'))
            return $this->GetData('config');
        else
            return NULL;
    }

    /**
     * Имя приложения
     * @return string
     */
    public function ApplicationName()
    {
        $app = $this->GetStr('app', NULL);
        if ($app != NULL) return $app;

        $name = $this->GetStr('module');
        $data = explode('\\', $name);
        $appAuto = $data[0];

        if ($appAuto != 'Core')
            return $appAuto;

        Internal\Tools::Error("Для встроенных модулей название приложения в роутинге необходимо указывать явно. Например:\n\nindex:\n  module: Core\Pages\n  app: Front");
        return null;
    }

    /**
     * @return Application
     * @access private
     */
    public function CreateApplicationInstance()
    {
        $appName = $this->ApplicationName();
        return Internal\ClassFactory::CreateApplication($appName);
    }

    /**
     * Создать модуль по параметрам роутинга и установить ему запрос
     * @return Controller
     * @access private
     */
    public function CreateModuleInstance()
    {
        $name = $this->GetStr('module');
        $action = $this->GetStr('action');

        $module = Internal\ClassFactory::CreateModule($name, $action);

        if ($module != null)
            $module->SetRoute($this);

        return $module;
    }
}