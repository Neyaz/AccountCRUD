<?

namespace Core\Internal;
use Core\PresentationType;

/**
 * Tools
 * @access private
 */
class Tools
{
    static function Initialize()
    {
        spl_autoload_register(array('\Core\Internal\Tools', 'AutoLoad'));
         // перехват критических ошибок
        if (\Core\Config::Instance()->IsProductionMode())
            register_shutdown_function(array('\Core\Internal\Tools', 'ErrorCheck'));
        ini_set('display_errors', !\Core\Config::Instance()->IsProductionMode());
    }

    /**
     * Проверить является ли утверждение истинным и если нет, вывести ошибку
     * @param bool $condition
     * @param string $message
     */
    static function Assert($condition, $message)
    {
        if (!$condition)
            self::Error($message);
    }

    /**
     * Спровоцировать ошибку и прекратить выполнение программы.
     * В режиме Debug выводит отладочную информацию на страницу.
     * В рабочем режиме - возвращает код 500
     */
    static function Error($message)
    {
        \Core\Response::ClearOutputBuffers();

        header("HTTP/1.0 500 LiteWork Error");

        if (!\Core\Config::Instance()->IsProductionMode())
        {
            if (\Core\Request::Instance()->IsAjax())
            {
                echo $message;
            }
            else
            {
                echo '<body bgcolor="#ffa6a6">';
                echo '<h1>LiteWork</h1>';
                echo "<h4>epic fail...</h4>\r\n";
                echo "<pre>$message</pre>\r\n";
                echo '</body>';
            }
        }
        else
        {
            if (!\Core\Request::Instance()->IsAjax())
                echo '<h1>LiteWork Error</h1>';
        }
        die;
    }

    /**
     * Включить PHP файл и вернуть его содержимое
     */
    static function GetIncludeContents($filename, $array = NULL)
    {
        if (\Core\Path::FileExist($filename))
        {
            ob_start();
            extract(\Core\Internal\Runtime::Instance()->InternalGetLayoutData()->GetItems(), EXTR_REFS);

            if ($array != NULL)
                extract($array, EXTR_REFS);

            include $filename;
            $contents = ob_get_contents();
            ob_end_clean();

            return $contents;
        }
        return false;
    }

    static function AutoLoad($ClassName)
    {
        $Core = \Core\Path::Relative('Core');

        if (class_exists($ClassName, false) || interface_exists($ClassName, false))
            return false;

        // Doctrine
        if (strpos($ClassName, 'Doctrine') !== false)
        {
            if (!defined('DOCTRINE_LOADED'))
            {
                define('DOCTRINE_LOADED', true);
                $classLoader = new \Doctrine\Common\ClassLoader('Doctrine', \Core\Path::Relative('Core', 'External', 'lib'));
                $classLoader->register();
                $classLoader->loadClass($ClassName);
                include_once \Core\Path::Combine($Core, 'External', 'DoctrineConfig.php');

                return false;
            } else {
                return false;
            }
        }

        // Core
        if (substr($ClassName, 0, 5) == 'Core\\')
        {
            $name = str_replace('\\', DIRECTORY_SEPARATOR, $ClassName);
            $path = \Core\Path::Relative($name.'.php');
            if (\Core\Path::FileExist($path))
            {
                include_once $path;
                return true;
            }
        }

        // Entities
        $path = \Core\Path::Relative('Entities', $ClassName.'.php');
        if (\Core\Path::FileExist($path))
        {
            include_once $path;
            return true;
        }
        $path = \Core\Path::Relative('Entities', 'Tables', $ClassName.'.php');
        if (\Core\Path::FileExist($path))
        {
            include_once $path;
            return true;
        }

        // Helpers
        $path = \Core\Path::Relative('Helpers', $ClassName, $ClassName.'.php');
        if (\Core\Path::FileExist($path))
        {
            include_once $path;
            return true;
        }
        $path = \Core\Path::Relative('Core', 'Helpers', $ClassName, $ClassName.'.php');
        if (\Core\Path::FileExist($path))
        {
            include_once $path;
            return true;
        }

        $nameItems = explode('\\', $ClassName);
        $pathItems = $nameItems;
        unset($pathItems[count($pathItems) - 1]);
        $className = $ClassName;
        $lastName = $nameItems[count($nameItems) - 1];
        $locations = array('Lib');

        foreach ($locations as $location)
        {
            $file = \Core\Path::Combine(\Core\Path::BasePath(), $location, $pathItems, $lastName.'.php');
            if (\Core\Path::FileExist($file))
            {
                include_once $file;
                return true;
            }
        }

        return false;
    }

    static function ErrorCheck()
    {
        $error = error_get_last();
        switch($error['type'])
        {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                \Core\Response::Error500($DebugErrorText);
                 break;
        }
    }

    /**
     * Получить тип по заданному имени представления
     * @param string $name
     * @return type PresentationType
     */
    static function PresentationNameToType($name)
    {
        if ($name == 'JSON')
            return \Core\Enum\PresentationType::JSON;
        if ($name == 'TEXT')
            return \Core\Enum\PresentationType::TEXT;
        if ($name == 'DENY')
            return \Core\Enum\PresentationType::DENY;
        return \Core\Enum\PresentationType::VIEW;
    }

    /**
     * Найти в массиве наиболее похожий элемент. Сначала идет сравнение с учетом строго типа (0 !== NULL, 0 !== ''),
     * если ни одного соответствия не найдено, то возвращается элемент совпадающий по значению (0 == '')
     * @param array $array
     * @param mixed $searchedValue искомое значение
     * @return mixed найденный элемент
     */
    static function FindEqualsItem($array, $searchedValue)
    {
        $candidateFound = false;
        $candidate = NULL;
        foreach ($array as $key => $val)
        {
            if ($val === $searchedValue)
                return $val;

            if ($val === '' && $searchedValue === NULL)
                return $val;

            if ($val === NULL && $searchedValue === '')
                return $val;

            if ($val == $searchedValue)
            {
                $candidateFound = true;
                $candidate = $val;
            }
        }

        if ($candidateFound)
            return $candidate;
        else
            return false;
    }

    static function ProcessListItems(&$array, &$params)
    {
        if (isset($params) && isset($params['empty']))
        {
            $ins = $params['empty'];
            if (is_array($ins))
                $array = \Core\ArrayTools::InsertFirstItem($array, key($ins), current($ins));
            else
                $array = \Core\ArrayTools::InsertFirstItem($array, NULL, $ins);
            unset($params['empty']);
        }
        return \Core\ArrayTools::ArrayToAttributesString($params);
    }

    /**
     * Получить список модулей
     */
    static function GetModules($appName)
    {
        $result = array();
        $path = \Core\Path::Relative($appName);
        if (is_dir($path))
        {
            $d = dir($path);
            while (false !== ($it = $d->read()))
            {
                if ($it == '.' || $it == '..' || !is_dir(\Core\Path::Relative($appName, $it))) continue;
                $result[] = $it;
            }
        }
        return $result;
    }

    static function FillFile($src, $dst, $data)
    {
        $txt = file_get_contents($src);
        foreach ($data as $key => $val)
        {
            $txt = str_replace("%$key%", $val, $txt);
        }
        file_put_contents($dst, $txt);
    }

    static function GetDirectoryItems($path)
    {
        $dir = dir($path);
        $apps = array();
        while (($cur = $dir->read()) !== false)
        {
            if($cur == "." || $cur == "..") continue;
            $apps[] = $cur;
        }
        return $apps;
    }

    /**
     * Инсталирован ли LiteWork для этого сайта
     * @return bool
     */
    static function IsInstalled()
    {
        return \Core\Path::FileExist(\Core\Path::Relative('Config.yml')) ||
            \Core\Path::FileExist(\Core\Path::Relative('Route.yml'));
    }

    /**
     * Регистронезависимое получение свойства
     * @param \ReflectionClass $reflection
     * @param string $name
     */
    static function GetProperty($reflection, $name)
    {
        foreach ($reflection->getProperties() as $prop)
        {
            if (\Core\Utils::CompareStrings($prop->getName(), $name, false) == 0)
                return $prop;
        }
        return false;
    }

    /**
     * Внутренняя функция для преобразования данных в массив
     * @access private
     * @param &array $result
     * @param array $data
     * @param &array $map
     * @param int $level Глубина рекурсии
     * @param string $resultPresentation
     * @return \stdClass
     */
    static function ExpandObjectsInternal(&$result, $data, &$map, $level, $resultPresentation = NULL)
    {
        if ($level < 0) return FALSE;

        if (is_array($data))
        {
            $tot = count($data);
            $repeats = 0;
            foreach ($data as $key => $value)
            {
                $calc = array();
                if (!self::ExpandObjectsInternal($calc, $value, $map, $level-1, $resultPresentation))
                {
                    unset($data[$key]); // Плохой ключ!
                    $repeats++;
                    continue;
                }
                $data[$key] = $calc;
            }
            if ($repeats == $tot && $tot != 0) return FALSE;
        }
        else if (is_object($data))
        {
            $hash = spl_object_hash($data);
            if (array_key_exists($hash, $map)) return FALSE;
            $map[$hash] = true;
            $reflect = new \ReflectionClass($data);

            if ($reflect->implementsInterface('Doctrine\ORM\Proxy\Proxy'))
                return FALSE;

            else if ($reflect->isSubclassOf('Core\BaseEntity'))
                $data = $data->ToArray($resultPresentation);

            else if ($reflect->getName() == 'Doctrine\ORM\PersistentCollection')
                if ($data->IsInitialized())
                    $data = $data->ToArray();
                else
                    return FALSE;

            else if ($reflect->getName() == 'DateTime')
                $data = $data->format(\DateTime::ISO8601);

            else if ($reflect->getName() == 'Core\ViewData')
                $data = $data->GetItems();

            else if (class_exists($reflect->getName()))
                $data = (array)$data;

            else
                return new \stdClass();

            $calc = array();
            if (!self::ExpandObjectsInternal($calc, $data, $map, $level, $resultPresentation))
                return FALSE;
            $data = $calc;
        }
        $result = $data;
        return TRUE;
    }
}

class ErrorRoute extends \Core\Route
{
    public function __construct($code)
    {
        $app = \Core\Config::Instance()->GetStr('error/app', NULL);
        $layout = \Core\Config::Instance()->GetStr('error/layout', NULL);
        Tools::Assert($app != NULL, 'В разделе конфигурации error не задан обязательный параметр <b>app</b>');

        $opt = array();
        $opt['error'] = $code;
        $opt['app'] = $app;
        $opt['module'] = \Core\Config::Instance()->GetStr('error/module', 'Core\Pages');
        $opt['action'] = \Core\Config::Instance()->GetStr('error/action', 'index');
        if ($layout != NULL)
            $opt['layout'] = $layout;
        parent::__construct('/error/'.$code, $opt);
    }
}

/**
 * @access private
 */
class RootModeRoute extends \Core\Route
{
    public function __construct()
    {
        $opt = array();
        $opt['module'] = 'Core\\RootMode';
        $opt['action'] = '%1';
        $opt['app'] = 'RootMode';
        parent::__construct('rootmode/*', $opt);
    }

    /**
     * @return Application
     */
    public function CreateApplicationInstance()
    {
        return new \Core\RootModeApplication();
    }
}