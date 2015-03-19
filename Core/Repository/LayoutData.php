<?

namespace Core\Repository;

/**
 * LayoutData
 */
class LayoutData
{
    private $__data = array();

    /**
     * Установить набор элементов массивом. Масив должен быть представлен в виде ключ-значение.
     * Перед установкой старые элементы не сбрасываются.
     * @param string[] $data
     * @return LayoutData Возвращает $this для возможности объявления цепочек вызовов
     */
    public function SetItems($data)
    {
        if ($data == NULL) return;

        foreach ($data as $name => $val)
        {
            $this->SetItem($name, $val);
        }
        return $this;
    }
    /**
     * Получить все элементы массивом. Масив будет представлен в виде ключ-значение.
     * @return string[]
     */
    public function GetItems()
    {
        return $this->__data;
    }

    /**
     * Установить значение элемента
     * @param string $name
     * @param string $value
     */
    public function SetItem($name, $value)
    {
        $this->__data[$name] = $value;
    }
    /**
     * Получить значение элемента
     * @param string $name
     * @return string
     */
    public function GetItem($name)
    {
        return $this->__data[$name];
    }

    function  __set($name,  $value)
    {
        $this->SetItem($name, $value);
    }
    function  __get($name)
    {
        return $this->__data[$name];
    }
}

?>
