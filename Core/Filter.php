<?

namespace Core;

/**
 * Filter
 * Позволяет привязать к заданному роутингу произвольную пред и пост обработку
 */
class Filter
{
    private $module;
    private $cfg;

    /**
     * @access private
     * @param Controller $module
     * @param array $config
     */
    public function Init($module, $config)
    {
        $this->module = $module;
        $this->cfg = $config;
        $this->OnInit();
    }

    public function GetConfigArray()
    {
        return $this->cfg;
    }

    public function GetConfigItem($name, $default = null)
    {
        if($this->cfg[$name] == null)
            return $default;
        else
            return $this->cfg[$name];
    }

    /**
     * Получить модуль к которому относится данный фильтр
     * @return Controller
     */
    public function Module()
    {
        return $this->module;
    }

    /**
     * Перегружаемая функция, вызывается при инициализации фильтра
     * @abstract
     */
    public function OnInit()
    {
    }

    /**
     * Вызывается перед выполнением основного модуля, выбранного роутингом
     */
    public function BeforeExecute()
    {
        return true;
    }

    /**
     * Вызывается после выполнения основного модуля, выбранного роутингом
     */
    public function AfterExecute()
    {
        return true;
    }

    /**
     * Вызывается перед отправкой ответа пользователю, непосредственно до заполнения Layout данными
     * К этому времени все подмодули также закончили свою работу.
     */
    public function BeforeResponse()
    {
        return true;
    }
}

?>
