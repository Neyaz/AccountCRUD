<?

namespace Core;

/**
 * Helper
 */
class Helper
{
    /**
     * Вызывать для создания указанного View представления.
     * Вызвается только пользователем.
     * @return ViewData
     */
    protected static function CreateView($viewName)
    {
        $className = 'Helpers\\' . get_called_class();
        $view = ViewData::Create($className, $viewName);
        return $view;
    }
}

?>
