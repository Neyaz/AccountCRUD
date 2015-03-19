<?php

namespace Core\Internal;

/**
 * Обрамление, шаблон страницы
 */
class LayoutManager
{
    static private $instance = NULL;

    private $name;

    /**
     * Синглетон
     * @return LayoutManager
     */
    static function Instance()
    {
        if (self::$instance == NULL)
            self::$instance = new LayoutManager();
        return self::$instance;
    }
    private function __construct()
    {
    }
    private function __clone()
    {
    }

    /**
     * Загрузить шаблон по имени
     */
    private function Apply($item)
    {
        $this->name = $item;
        $this->InitLayout();
    }

    /**
     * Установить пустой (нулевой) шаблон.
     */
    private function ApplyNull()
    {
        $this->name = '';
        $this->InitLayout();
    }

    private function InitLayout()
    {
        if ($this->name != '' && !\Core\Path::FileExist($this->FullPath()))
            \Core\Internal\Tools::Error("Layout '$this->name' not found");
    }

    /**
     * Получить выбранный шаблон
     */
    public function Current()
    {
        return $this->name;
    }

    public function IsNull()
    {
        return $this->name == '';
    }

    /**
     * Установить параметры Layout в соответствии с указаниями роутинга
     * @param Route $route
     */
    public function ApplyAutomatic($name)
    {
        if (\Core\Request::Instance()->IsAjax())
        {
            $this->ApplyNull();
            return;
        }

        if (strcasecmp($name, 'NULL') == 0)
            $layout = '';
        else if ($name != '')
            $layout = ucfirst($name);
        else
            $layout = \Core\LiteWork::Instance()->CurrentRoute()->ApplicationName();

        if ($layout != '')
            LayoutManager::Instance()->Apply($layout);
        else
            LayoutManager::Instance()->ApplyNull();
    }

    /**
     * Полный путь к выбранному шаблону
     * @return string
     */
    public function FullPath()
    {
        $n = $this->name;
        if ($n == 'RootMode')
            return \Core\Path::Relative('Core', 'Data', 'Layouts', $n.'Layout.php');

        return \Core\Path::Relative('Layouts', $n.'.php');
    }
}

?>
