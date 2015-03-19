<?
/*
 * LiteWork - Fast PHP MVC Framework
 * Copyright (c) 2012, ООО "66 Бит"
 * http://66bit.ru
 */

namespace Core;

$path = __DIR__ . DIRECTORY_SEPARATOR;
require_once $path.'Global.php';

class LiteWork
{
    /**
     * @var Application
     */
    private $selectedApp = null;
    /**
     * @var Controller
     */
    private $selectedModule = null;
    /**
     * @var Route
     */
    private $selectedRoute = null;

    //TODO
    private $filters = array();

    private $routing = NULL;

    static private $instance = NULL;

    /**
     * Синглетон
     * @return LiteWork
     */
    static function Instance()
    {
        if (self::$instance == NULL)
            self::$instance = new LiteWork();
        return self::$instance;
    }
    private function __clone()
    {
    }
    private function __construct()
    {
        if (!Path::FileExist(Path::Relative('Route.yml'))) throw new LiteWorkInitializationException();
        $this->routing = \Symfony\Component\Yaml\Yaml::parse(Path::Relative('Route.yml'));
    }

    /**
     * Инициализация LiteWork
     */
    public function Initialize()
    {
        Config::Instance()->LoadLayoutDefaultParameters();

        $routes = \Core\Internal\Runtime::GetSuitableRoutesConfig($this->routing);

        foreach ($routes as $path => $config)
        {
            $route = new Route($path, $config);
            if ($this->SetRouteConfig($route))
                break; // Соответствующий роутингу модуль найден
        }

        if ($this->selectedModule == null)
        {
            if (!$this->ExecuteRootMode())
                Response::Error404('В соответствии с роутингом не найден ни один подходящий модуль');
        }
    }

    /**
     * Запустить основной модуль и все подмодули на выполнение, а затем сформировать ответ пользователю.
     */
    public function Execute()
    {
        $this->CurrentApplication()->BeforeExecute();
        Internal\Runtime::Instance()->RunModule($this->selectedModule, $this->selectedRoute, true);
        $this->CurrentApplication()->AfterExecute();
        Internal\Runtime::Instance()->ExecuteResponse();
    }

    /**
     * Применить конфигурацию роутинга в явном виде
     * @param Route $route
     * @access private
     */
    public function SetRouteConfig($route)
    {
        $appName = $route->ApplicationName();

        if ($this->selectedRoute == NULL || $this->selectedRoute->ApplicationName() != $appName)
        {
            $app = $route->CreateApplicationInstance();
            if (!$app->Check())
                return false;
        }
        else
            $app = $this->selectedApp;

        $oldApp = $this->selectedApp;
        $oldRoute = $this->selectedRoute;

        // Временно применяем, чтобы модуль знал что выбраны его любимые классы
        $this->selectedApp = $app;
        $this->selectedRoute = $route;

        // Загружаем модуль для проверки
        $module = $route->CreateModuleInstance();

        // Проверяем
        if ($module->IsRouteMethodExist() && $module->Check())
        {
            $this->selectedModule = $module;
            return true;
        }

        // Если модуль не подошел вренем старые значения
        $this->selectedApp = $oldApp;
        $this->selectedRoute = $oldRoute;

        return false;
    }

    protected function ExecuteRootMode()
    {
        if (Request::Instance()->GetAbsoluteItemData(1) == 'rootmode')
        {
            $route = new Internal\RootModeRoute();

            $ok = LiteWork::Instance()->SetRouteConfig($route);
            if ($ok)
            {
                LiteWork::Instance()->Execute();
                die;
            }
        }
    }

    /**
     * Возвращает выбранный роутинг
     * @return Route
     */
    public function CurrentRoute()
    {
        return $this->selectedRoute;
    }

    /**
     * Возвращает выбранный основной модуль
     * @return Controller
     */
    public function CurrentModule()
    {
        return $this->selectedModule;
    }

    /**
     * Возвращает текущее приложение
     * @return \Core\Application
     */
    public function CurrentApplication()
    {
        return $this->selectedApp;
    }

    public function GetUserApplications()
    {
        $dir = dir(Path::BasePath());
        $apps = array();
        while (($cur = $dir->read()) !== false)
        {
            if (!is_dir($cur)) continue;
            if ($cur[0] < 'A' || $cur[0] > 'Z') continue;
            if($cur == "Core") continue;
            if($cur == "Layouts") continue;
            if($cur == "Database") continue;
            if($cur == "Lib") continue;
            if($cur == "Pages") continue;
            if($cur == "Locales") continue;
            if($cur == "Entities") continue;
            if($cur == "Templates") continue;
            if($cur == "." || $cur == "..") continue;
            if (!Path::FileExist(Path::Relative($cur, $cur.'Application.php'))) continue;
            $apps[] = $cur;
        }
        return $apps;
    }

    /**
     * Версия фрамеворка
     */
    public function GetVersion()
    {
        return "4.0 Beta 3";
    }
}