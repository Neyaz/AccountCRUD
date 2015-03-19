<?

namespace Core\Internal;

/**
 * ClassFactory
 */
class ClassFactory
{
    /**
     * Создать приложение по имени
     * @param string $name
     * @return \Core\Application
     * @access private
     */
    static function CreateApplication($name)
    {
        $name = ucfirst($name);
        $className = \Core\Application::GetApplicationClassName($name);
        $file = \Core\Path::Relative($name, $className . '.php');
        if (!\Core\Path::FileExist($file))
            \Core\Internal\Tools::Error("Файл с классом приложения '$name' не найден.\nСоздайте файл:\n\n".$file);

        require_once $file;

        if (!class_exists($className))
            \Core\Internal\Tools::Error("Невозможно загрузить приложение '$name', класс $className отсутствует в файле $file");

        if (!is_subclass_of($className, 'Core\Application'))
            \Core\Internal\Tools::Error("Невомзожно загрузить класс приложения '$className' - базовый класс должен быть '\Core\Application'");

        $app = new $className();
        return $app;
    }

    /**
     * Создать модуль
     * @return Controller
     * @access private
     */
    static function CreateModule($moduleName, $action)
    {
        $nameItems = explode('\\', $moduleName);
        $className = $moduleName;
		$modPathName = str_replace('\\', DIRECTORY_SEPARATOR, $moduleName);
        $lastName = $nameItems[count($nameItems) - 1];

        $file = \Core\Path::Relative($modPathName, $lastName.'.php');
        $userFile = $file;
        if (\Core\Path::FileExist($file))
            require_once $file;
        else if (count($nameItems) > 1 && $nameItems[0] == 'Core')
        {
            $file = \Core\Path::Relative('Core', 'Modules', $lastName, $lastName .'.php');
            if (\Core\Path::FileExist($file))
                require_once $file;
        }
        else
            \Core\Internal\Tools::Error("Файл модуля '$moduleName' не найден.\nСоздайте файл модуля:\n\n".$userFile);

        if (!class_exists($className))
            \Core\Internal\Tools::Error("Невозможно загрузить модуль '$moduleName', класс модуля отсутствует в файле $file");

        $module = new $className();

        if (!is_subclass_of($module, 'Core\Controller'))
            \Core\Internal\Tools::Error("Невомзожно загрузить модуль '$moduleName' - базовый класс должен быть 'Core\Controller'");

        $module->SetAction($action);

        return $module;
    }

    /**
     * Создать фильтр по имени
     * @param string $name
     * @return Filter
     */
    public static function CreateFilter($name, $module, $config)
    {
        $nameItems = explode('\\', $name);
        $className = $name;
        $lastName = $nameItems[count($nameItems) - 1];
        $relPath = implode(DIRECTORY_SEPARATOR, $nameItems);

        $path = \Core\Path::Relative('Filters', $relPath, $className.'.php');
        if (\Core\Path::FileExist($path))
            require_once $path;
        else if (count($nameItems) > 1 && $nameItems[0] == 'Core')
        {
            $path = \Core\Path::Relative('Core', 'Filters', $lastName, $lastName.'.php');
            if (\Core\Path::FileExist($path))
                require_once $path;
        }
        else
            \Core\Internal\Tools::Error("Filter '$name' not found");

        if (!class_exists($name))
            \Core\Internal\Tools::Error("Can't load filter '$name' - no filter class in file: $path");

        $filter = new $name();
        $filter->Init($module, $config);

        if (!is_subclass_of($filter, 'Core\Filter'))
            \Core\Internal\Tools::Error("Can't load filter '$name' - parent class must be 'Filter'");

        return $filter;
    }
}