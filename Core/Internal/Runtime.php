<?

namespace Core\Internal;

/**
 * Runtime
 */
class Runtime
{
    static private $instance = NULL;

    /**
     * Синглетон
     * @return Runtime
     */
    static function Instance()
    {
        if (self::$instance == NULL)
            self::$instance = new Runtime();
        return self::$instance;
    }
    private function __construct()
    {
        $this->layoutData = new \Core\Repository\LayoutData();
    }
    private function __clone()
    {
    }

    /**
     * @var \Core\Repository\LayoutData
     */
    private $layoutData;

    private $filters = array();

    /**
     * @param \Core\Controller $module
     * @param \Core\Route $route
     */
    public function RunModule($module, $route, $mainMod)
    {
        $action = $module->GetAction();
        $modName = $module->ModuleTypeName();
        $appName = $route->ApplicationName();
        $requestType = \Core\Request::Instance()->RequestType();

        $presentation = AnnotationManager::Instance()->GetResultPresentationName($action, $modName, $appName, $requestType);
        if ($presentation == NULL)
            \Core\Response::Error403("Недопустимый тип запроса '$requestType'. Необходимо явно разрешить соответствующий тип запроса.");

        $layout = AnnotationManager::Instance()->GetLayout($action, $modName, $appName);

        $configData = AnnotationManager::Instance()->GetFilters($action, $modName, $appName);
        $filters = array();

        if ($configData != false)
        {
            foreach ($configData as $name => $params)
            {
                $filters[] = ClassFactory::CreateFilter($name, $module, $params);
            }
        }

        $ok = true;
        foreach ($filters as $it)
            $ok &= $it->BeforeExecute();

        $argsInfo = AnnotationManager::Instance()->GetArgumentsInfo($action, $modName, $appName);
        $result = $this->InternalExecute($module, $argsInfo);

        $ok = true;
        foreach ($filters as $it)
            $ok &= $it->AfterExecute();

        $this->filters = array_merge($this->filters, $filters);


        if (is_a($result, 'Core\Repository\ResultData'))
        {
            $type = \Core\Internal\Tools::PresentationNameToType($presentation);

            switch ($type)
            {
                case \Core\Enum\PresentationType::JSON:
                    $output = \Core\Utils::ToJSON($result->GetItems());
                    $layout = 'NULL';
                    break;
                case \Core\Enum\PresentationType::TEXT:
                    $output = $result->GetTextData();
                    break;
                case \Core\Enum\PresentationType::VIEW:
                    $view = \Core\ViewData::Create($module, $presentation);
                    $attrs = $result->GetItems();
                    $view->SetItems($attrs);
                    $output = $view->ToHtml();
                    if (\Core\Config::Instance()->IsDebug())
                        DebugManager::AddView($view->GetFileName(), $view->GetItems());
                    break;
                case \Core\Enum\PresentationType::DENY:
                    \Core\Response::Error403('PresentationType::DENY');
                    break;
                default:
                    throw new Exception('Not supported type');
            }
        }
        else
            \Core\Response::Error403("Не определен ResultData. Возможно Вы забыли вызвать родительский конструктор в контроллере или переписали значение?");

        if ($mainMod)
            LayoutManager::Instance()->ApplyAutomatic($layout);
        $this->InternalGetLayoutData()->SetItem($module->GetDestination(), $output);
    }

    /**
     * Выполнить модуль
     * @access private
     * @param \Core\Controller $module
     */
    private function InternalExecute($module, $argumentsInfo)
    {
        $call = \Core\Controller::GetMethodNameFromAction($module->GetAction());
        $refl = new \ReflectionClass($module);
        if ($refl->hasMethod($call))
        {
            $module->BeforeExecute();
            $method = $refl->getMethod($call);
            $k = $method->getNumberOfParameters();
            $args = $this->CreateArgmuentsArray($argumentsInfo, $k);
            if ($args === false)
                \Core\Response::Error404();

            if ($args != NULL)
                $returned = call_user_func_array(array($module, $call), $args);
            else
                $returned = $module->$call();
            $module->AfterExecute();

            if (is_string($returned) || is_numeric($returned))
                $module->Result()->AddTextData($returned);
            else if (is_array($returned))
                $module->Result()->SetItems($returned);

            return $module->Result();
        }
        else
        {
            \Core\Response::Error404('!method_exists(' . $module->GetModuleName() . " $call)");
            return null;
        }
    }

    private function CreateArgmuentsArray($argumentsInfo, $methodParamsCount)
    {
        if ($argumentsInfo === FALSE)
        {
            if ($methodParamsCount == 0)
                return array();
            else
                Tools::Error('Для метода принимающего параметры, необходимо объявить анотацию @arguments. Пример: @arguments [%1:string, %2:int, %3:bool=false]');
        }

        if (count($argumentsInfo) != $methodParamsCount)
            Tools::Error('Неправильное объявление анотации @arguments - не совпадает количество параметров');

        $result = array();
        foreach ($argumentsInfo as $it)
        {
            $id = intval($it['value']);
            switch ($it['type'])
            {
                case 'string':
                    $val = \Core\Request::Instance()->GetRelativeItemStr($id, false);
                    break;
                case 'int':
                case 'integer':
                    $val = \Core\Request::Instance()->GetRelativeItemInt($id, false);
                    break;
                default:
                    Tools::Error('Неизвестный тип аргумента: ' . $it['type']);
                    break;
            }
            if ($val === false)
            {
                if ($it['required'])
                    return false;
                else
                {
                    $val = $it['default'];
                    if ($it['type'] == 'int')
                        $val = intval($val);
                }
            }

            $result[] = $val;
        }

        return $result;
    }

    static function GetSuitableRoutesConfig($configs)
    {
        $result = array();

        $request = \Core\Request::Instance()->AbsoluteItemsArray();
        $requestDomain = \Core\Request::Instance()->DomainName(\Core\Enum\DomainNameMode::WITHOUT_WWW);

        // Определяем какой модуль будет заниматься обработкой запроса.
        foreach ($configs as $path => $val)
        {
            if ($path[0] == '(')
            {
                if (self::CheckDomain($requestDomain, $path))
                {
                    foreach ($val as $domainPath => $domainConfig)
                    {
                        if(self::CheckRoute($domainPath, $domainConfig, $request))
                            $result[$domainPath] = $domainConfig;
                    }
                }
            }
            else
            {
                if(self::CheckRoute($path, $val, $request))
                    $result[$path] = $val;
            }
        }

        return array_reverse($result);
    }

    static function CheckDomain($requestDomain, $path)
    {
        $domainsData = \Core\Utils::SubstringMid($path, 1, -1);
        $domains = \explode('|', $domainsData);
        foreach ($domains as $domain)
        {
            $domain = \trim($domain);
            \Core\Internal\Tools::Assert(\strpos($domain, '.'), 'Имя домена в роутинге должно содержать точку (можно просто в конце): '.$domain);
            if (\strncasecmp($requestDomain, $domain, \strlen($domain)) == 0)
                return true;
        }

        return false;
    }

    static function CheckRoute($path, $val, $request)
    {
        if (isset($val['enabled']) && !\Core\Utils::ToBool($val['enabled'])) continue;
        $requestCount = count($request) - 1;

        $route = explode('/', $path);
        $routeCount = count($route);
        $ok = $routeCount <= $requestCount+1; // Роутинг должен быть не длинее запроса
        for($i = 0; $ok && $i<$routeCount && $i<$requestCount; $i++)
        {
            if ($route[$i] == $request[$i + 1])
            {
                // Ok = aa/bb/cc
            } else if ($route[$i] == '*' && $ok)
            {
                // Ok + Subpages = aa/bb/*
                break;
            } else {
                // Error
                $ok = false;
                break;
            }
        }
        if ($routeCount != $requestCount && $route[$routeCount-1]!='*')
            $ok = false;

        return $ok;
    }

    /**
     * Внутренний метод
     * @access private
     */
    public function InternalGetLayoutData()
    {
        return $this->layoutData;
    }

    /**
     * Вывести выбранный шаблон, подставив содержимое
     */
    public function ExecuteResponse()
    {
        // Submodules
        $app = \Core\LiteWork::Instance()->CurrentApplication();
        $submods = $app->SubModulesArray();
        $route = \Core\LiteWork::Instance()->CurrentRoute();
        foreach ($submods as $it)
        {
            self::Instance()->RunModule($it, $route, false);
        }

        // Filters BeforeResponse
        $ok = true;
        foreach ($this->filters as $it)
            $ok &= $it->BeforeResponse();

        // Build debug info
        if (\Core\Config::Instance()->IsDebug())
        {
            DebugManager::DumpFilters($this->filters);
            DebugManager::DebugDump();
        }

        // Output Content
        $lm = \Core\Internal\LayoutManager::Instance();
        if ($lm->Current() == '')
        {
            echo Runtime::Instance()->InternalGetLayoutData()->content;
            return;
        }
        $lauout = \Core\Internal\Tools::GetIncludeContents(\Core\Internal\LayoutManager::Instance()->FullPath());
        if ($lauout !== false)
            echo $lauout;
        else
            \Core\Internal\Tools::Error('Не удается загрузить Layout: '.$lm->Current());

        // Output Debug
        if (!$lm->IsNull() && \Core\Config::Instance()->IsDebug())
            DebugManager::OutputWriteDebugInfo();
    }

    static function ExecuteErrorMode()
    {
        if (isset($_GET['GoToInstall']))
        {
            self::Install();
        }

        if (!Tools::IsInstalled())
        {
            echo '<!DOCTYPE HTML>';
            echo '<html><head><title>Install LiteWork</title><meta charset="utf-8"/></head><body>';
            echo 'Unable to find any of the core files.';
            echo '<form action="/">';
            echo 'Jenkins host: <input type="text" name="ci_host">';
            echo 'Jenkins port: <input type="text" name="ci_port">';
            echo 'Jenkins job: <input type="text" name="ci_job">';
            echo '<input type="hidden" name="GoToInstall" value="1">';
            echo '<input type="submit" name="submit" value="Создать">';
            echo '</form>';
            echo '<br/><small>(Be aware that all files will be overwritten)</small>';
            echo '</body></html>';
            die;
        }
    }

    static function Install()
    {
        if (Tools::IsInstalled()) return;
        \Core\Path::CopyDirectory(\Core\Path::Relative('Core', 'Data', 'Default'), \Core\Path::BasePath());
        rename(\Core\Path::Relative('original.htaccess'), \Core\Path::Relative('.htaccess'));
        $file = fopen ("http://code.jquery.com/jquery.js", "r");
        while ($tmp = fread($file, 1024))
            $jquery .= $tmp;
        file_put_contents(\Core\Path::Relative('web', 'js', 'jquery-core.js'), $jquery);

        $cfgFile = \Core\Path::Relative('Config.yml');
        $cfgSrc = \Core\Path::Relative('Core', 'Data', 'Templates', 'Install', 'Config.yml');
        $uniq = strtoupper(md5(md5(uniqid('LW', true)) . md5(serialize($_SERVER))));
        $pass = \Core\Utils::GeneratePassword(12, true, true, true);
        $p_hash = \Core\Utils::MakePasswordHash($pass, $uniq);


        if($_GET['ci_host'] && $_GET['ci_job'] && $_GET['ci_port'])
        {
            $jenkins = 'jenkins:' . "\n" .
                            '  domain: ' . $_GET['ci_host'] . "\n" .
                            '  job: ' . $_GET['ci_job'] . "\n" .
                            '  port: '. $_GET['ci_port'];

            \Core\Internal\Jenkins\Jenkins::CreateJob($_GET['ci_host'], $_GET['ci_port'], $_GET['ci_job']);

            $file = \Core\Path::Relative('../build.xml');
            $src = \Core\Path::Relative('Core', 'Data', 'Templates', 'Install', 'build.xml');
            Tools::FillFile($src, $file, array('project' => $_GET['ci_job']));

            $file = \Core\Path::Relative('../codecept.phar');
            $codecept = file_get_contents('http://codeception.com/codecept.phar');
            file_put_contents($file, $codecept);

            exec('cd ../ && php codecept.phar bootstrap' );
        }
        else
            $jenkins = '';

        Tools::FillFile($cfgSrc, $cfgFile, array('salt' => $uniq, 'rootmode_hash' => $p_hash, 'jenkins' => $jenkins));

        $pAccountEntity = \Core\Path::Relative("Entities", "Account.txt");
        rename(\Core\Path::Relative("Entities", "Account.txt"), \Core\Path::Relative("Entities", "Account.php"));
        rename(\Core\Path::Relative("Lib", "AppUser.txt"), \Core\Path::Relative("Lib", "AppUser.php"));

        echo '<!DOCTYPE HTML>';
        echo '<html><head><title>Install LiteWork</title><meta charset="utf-8"/></head><body>';
        echo '<h1>Installation completed</h1>';
        echo "Root Mode password:<h2><pre>$pass</pre></h2>";
        echo '<small>Save the password in order to avoid the doomsday.</small><br/><br/>';
        echo '<small>If you use Jenkins, set SVN path in CI system.</small><br/><br/>';
        echo '<a href="/">Start using LiteWork</a>';
        echo '</body></html>';
        die;
    }
}
