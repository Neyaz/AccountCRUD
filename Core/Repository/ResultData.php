<?

namespace Core\Repository;

/**
 * ResultData
 */
class ResultData
{
    private $__text;

    /**
     * Установить набор элементов массивом. Масив должен быть представлен в виде ключ-значение.
     * Перед установкой старые элементы не сбрасываются.
     * @param string[] $data
     * @return ResultData Возвращает $this для возможности объявления цепочек вызовов
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

        $this->__text = NULL;
    }

    /**
     * Установить текстовое содержимое
     * (вместо массива параметров)
     * @param string $text
     * @param boolean $escapeHtml Экранировать ли Html-тэги?
     */
    public function SetTextData($text, $escapeHtml = false)
    {
        $this->Reset();
        $this->AddTextData($text, $escapeHtml);
    }
    
    /**
     * Добавить текстовое содержимое в конец сформированной строки
     * @param string $text
     * @param boolean $escapeHtml Экранировать ли Html-тэги?
     */
    public function AddTextData($text, $escapeHtml = false)
    {
        $text = strval($text);
        if($escapeHtml)
            $text = htmlentities($text);
        $this->__text .= $text;
    }

    /**
     * Получить установленное текстовое содержимое
     * @return string
     */
    public function GetTextData()
    {
        return $this->__text;
    }
}

?>
