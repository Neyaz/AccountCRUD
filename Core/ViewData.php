<?

namespace Core;

/**
 * Хранит данные представленные во въюшке и
 * позволяет отображать её.
 */
class ViewData
{
    private $__filename;

    public function __construct($fullViewFileName)
    {
        $this->__filename = $fullViewFileName;
        \Core\Internal\Tools::Assert((Path::FileExist($fullViewFileName) && is_readable($fullViewFileName)), "View файл $fullViewFileName не существует.");
    }

    /**
     * Создать объект вьюшки для заданного класса
     * @param mixed $workClass Имя класса или объект для которого создается въюшка
     * @param string $viewName Имя въюшки
     * @return ViewData
     */
    static function Create($workClass, $viewName)
    {
        if (is_object($workClass))
        {
            $workClassName = get_class($workClass);
            $reflection = new \ReflectionClass($workClassName);
            $file = $reflection->getFileName();
            $path = dirname($file);
        }
        else
            $path = \Core\Path::Combine(\Core\Path::BasePath(), explode('\\',$workClass));

        // Подключение вьюшек из поддиректорий
        $pathSectors = explode("/", $viewName);
        $viewName = end($pathSectors);
        unset($pathSectors[count($pathSectors)-1]);

        if (substr($viewName, 0, 4) != 'View')
            $viewName = 'View' . $viewName;

        return new ViewData(Path::Combine($path, $pathSectors, $viewName.'.php'));
    }

    /**
     * Установить набор элементов массивом. Масив должен быть представлен в виде ключ-значение.
     * Перед установкой старые элементы не сбрасываются.
     * @param string[] $data
     * @return ViewData Возвращает $this для возможности объявления цепочек вызовов
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
     * Устанавливает переменную для вида
     * @param string $key название переменной
     * @param string $value значение переменной
     * @return ViewData Возвращает $this для возможности объявления цепочек вызовов
     */
    public function SetItem($name, $value)
    {
        $this->$name = $value;

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
     * Сбросить содержимое
     */
    public function Reset()
    {
        $reflect = new \ReflectionClass($this);
        $vars = get_object_vars($this);
        foreach ($vars as $key => $val)
            if (!$reflect->hasProperty($key))
                unset ($this->$key);

        $__text = NULL;
    }

    /**
     * Преобразовать в строку, загрузив представление
     * @return string
     */
    public function ToHtml()
    {
        $data = $this->GetItems();

        return \Core\Internal\Tools::GetIncludeContents($this->__filename, $data);
    }

    public function GetFileName()
    {
        return $this->__filename;
    }

    public function __toString()
    {
        return $this->ToHtml();
    }
}

?>
