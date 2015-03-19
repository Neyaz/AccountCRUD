<?

namespace Core;

use \Core\Internal\Jenkins\Jenkins;

/**
 * RootMode
 */
class RootMode extends Controller
{
    private $config_name = '___rootmode';

    public function Check()
    {
        return Config::Instance()->GetBool('rootmode/enabled', false);
    }

    public function BeforeExecute()
    {
        $this->LayoutData()->version = LiteWork::Instance()->GetVersion();
        $this->LayoutData()->title = 'RootMode :]';
        $path = Request::Instance()->PathAfterRoute();
        
        if ($path != 'authorize')
        {
            $h = User::Instance()->GetStr($this->config_name);
            if ($h != md5(Config::Instance()->GetStr('rootmode/hash') . User::Instance()->IP()))
                Response::Redirect(':authorize');
        }
    }

    /**
     * @layout NULL
     */
    function OnAuthorize()
    {
        $pwd = $this->RequestData()->GetStr('value', false);
        if ($pwd != false)
        {
            User::Instance()->SetItem($this->config_name, $this->GetHash($pwd));
            sleep(2);
            Response::Redirect(':');
        }
    }

    function OnExit()
    {
        User::Instance()->DeleteItem($this->config_name);
        Response::Redirect(':');
    }

    function OnIndex()
    {
        $this->LayoutData()->sector = 'index';
        $apps = LiteWork::Instance()->GetUserApplications();

        $modes = array('dev', 'test', 'prod');
        foreach($modes as $key=>$mode)
        {
            if($mode == \Core\Config::Instance()->GetStr('mode'))
            {
                unset($modes[$key]);
                break;
            }
        }

        $this->Result()->modes = $modes;
        $this->Result()->modesText =  array('dev'=>'Разработка', 'test'=>'Тестирование', 'prod'=>'Рабочий режим');

        $this->Result()->applications = array();
        foreach ($apps as $app)
        {
            $all = Internal\Tools::GetModules($app);

            $v = $this->CreateView('App');
            $v->name = $app;
            $v->modules = $all;
            $this->Result()->applications[] = $v;
        }

        $this->GenerateHelpersView();

    }

    function OnDatabase()
    {
        $this->LayoutData()->sector = 'database';
        $processor = new \Core\Internal\EntityProcessor();
        $this->Result()->errors = $processor->Validate();
        $this->Result()->entities = array();
        $this->Result()->production = Config::Instance()->IsProductionMode();

        $items = $processor->GetStructure();
        foreach ($items as $key => $val)
        {
            $v = $this->CreateView('Entity');
            $v->name = $key;
            $v->items = $val;
            $this->Result()->entities[] = $v;
        }

        $split = 4;
        if ($split > count($items)) $split = max(count($items), 1);
        $this->Result()->split = $split;
    }

    function OnLoadData()
    {
        $path = Path::Relative('Entities', 'Fixtures');
        $list = Internal\Tools::GetDirectoryItems($path);
        $ep = new Internal\EntityProcessor();

        $result = array();
        foreach ($list as $it)
        {
            $file = Path::Combine($path, $it);
            $map = \Symfony\Component\Yaml\Yaml::parse($file);
            $result[] = $map;
        }
        $ep->LoadData($result);

        Response::Redirect(':database');
    }

    function OnUpdateDatabase()
    {
        $this->LayoutData()->sector = 'database';
        $em = Database::Main();
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $metadatas = $em->getMetadataFactory()->getAllMetadata();

        //$canWrite = !Config::Instance()->IsProductionMode();
        $canWrite = Request::Instance()->IsPost();
        $sqlLines = $schemaTool->getUpdateSchemaSql($metadatas, $canWrite);

        if ($canWrite)
        {
            $schemaTool->updateSchema($metadatas, $canWrite);
            Response::Redirect(':database');
        }
        else
        {
            $queries = '';
            foreach ($sqlLines as $sql)
                $queries .= $sql . ";<br/><br/>";//<br/>\r\n<br/>\r\n
        }

        $this->Result()->queries = \Core\Utils::SubstringLeft($queries, mb_strlen($queries)-10);
        $this->Result()->em = $em;

    }

    /**
     * @default EntitiesGenerator
     * @arguments [%2:string='', %3:string='']
     */
    function OnEntitiesGenerator($action, $name)
    {
        $this->Result()->newEntities = \Core\Internal\MetadataFromDatabase::FindNewDatatbaseEntities();
        $this->Result()->newFieldsList = \Core\Internal\MetadataFromDatabase::FindNewDatatbaseFields();
        if ($action == 'add-from-db')
        {
            $ep = new Internal\EntityProcessor();
            $ep->GenerateMappingByDatabase($name);
            \Core\Response::Redirect(':database');
        }
        if ($action == 'create-fields')
        {
            $tableName = \Core\Internal\MetadataManager::Instance()->GetTableName($name);
            $dbEntity = \Core\Internal\MetadataFromDatabase::GetEntityNameByTableName($tableName);
            $meta = \Core\Internal\MetadataFromDatabase::GetDBMetadataEntity($dbEntity);
            $ep = new Internal\EntityProcessor();
            $code = $ep->GenerateEntityFieldMappingProperties($meta);

            $originalMeta = \Core\Internal\MetadataManager::Instance()->GetMetadata($name);
            $up = new \Core\Internal\ClassUpdater($originalMeta->GetReflectionClass()->getName());
            $up->InsertCode($code);
            \Core\Response::Redirect(':database');
        }
    }

    /**
     * @default TEXT
     */
    function OnCreateProjectItem()
    {
        $type = $this->RequestData()->GetStr('type');
        $nameSrc = $this->RequestData()->GetStr('name');
        $name = Utils::DashedToCamelCase($nameSrc);
        if ($name == '')
        {
            $this->Result()->SetTextData('<p class="error">Не задано имя.</p>');
            return;
        }

        if ($type == 'module')
        {
            $app = $this->RequestData()->GetStr('app');
            $path = Path::Relative($app, $name);
            if (is_dir($path))
            {
                $this->Result()->SetTextData('<p class="error">Такой модуль уже существует.</p>');
                return;
            }

            mkdir($path, 750);
            $file = Path::Combine($path, $name.'.php');
            $src = Path::Relative('Core', 'Data', 'Templates', 'Module', 'Controller.txt');
            Internal\Tools::FillFile($src, $file, array('name' => $name, 'app' => $app));
            copy(Path::Relative('Core', 'Data', 'Templates', 'Module', 'ViewIndex.php'), Path::Combine($path, 'ViewIndex.php'));
        }
        else if ($type == 'app')
        {
            $path = Path::Relative($name);
            if (is_dir($path))
            {
                $this->Result()->SetTextData('<p class="error">Такое приложение уже существует.</p>');
                return;
            }

            mkdir($path, 750);
            $file = Path::Combine($path, $name . 'Application.php');
            $src = Path::Relative('Core', 'Data', 'Templates', 'Application', 'Application.txt');
            Internal\Tools::FillFile($src, $file, array('name' => $name));
        }
        else if ($type == 'helper')
        {
            if(\Core\Utils::SubstringRight($name, 6)!=="Helper")
                $name.="Helper";

            if(!is_dir(Path::Relative('Helpers')))
                mkdir(Path::Relative('Helpers'), 750);

            $path = Path::Relative('Helpers', $name);
            if (is_dir($path))
            {
                $this->Result()->SetTextData('<p class="error">Такой хелпер уже существует.</p>');
                return;
            }

            mkdir($path, 750);
            $file = Path::Combine($path, $name.'.php');
            $src = Path::Relative('Core', 'Data', 'Templates', 'Helper', 'Helper.txt');
            Internal\Tools::FillFile($src, $file, array('name' => $name));
        }
        Response::Redirect(':');
    }

    /**
    * @arguments [%2:string]
    */
    function OnSwitchMode($mode)
    {
        $allowed = array("prod", "dev", "test");
        if(!in_array($mode, $allowed))
            \Core\Response::Error403();

        $modes = array();
        foreach($allowed as $cMode)
        {
            if($mode != $cMode)
                $modes[] = $cMode;
        }

        $file = file_get_contents(\Core\Path::Relative('Config.yml'));
        $strings = explode("\n", $file);
        foreach($strings as $key => $string)
        {

            if(mb_strpos($string, 'mode') === 0)
            {

                $strings[$key] = 'mode: '.$mode . ' # '. implode(', ', $modes);
                break;

            }
        }
        $file = implode("\n", $strings);

        file_put_contents(\Core\Path::Relative('Config.yml'), $file);

        \Core\Response::Redirect(":");

    }

    /**
     * @default TEXT
     */
    function OnCreateMapping()
    {
        $ep = new Internal\EntityProcessor();
        $ep->GenerateMappingByDatabase();
    }

    /**
     * @default TEXT
     */
    function OnPhpInfo()
    {
        $this->LayoutData()->sector = 'php-info';
        ob_start();
        echo phpinfo();
        $txt = ob_get_contents();
        ob_end_clean();
        $this->Result()->SetTextData($txt);
    }

    function OnEntitiesAutogenerate()
    {
        $processor = new \Core\Internal\EntityProcessor();
        $processor->BuildMethods();
        $processor->BuildTables();
        $processor->GenerateProxies();
        Response::Redirect(':database');
    }

    function OnCss()
    {
        Response::SendFile(Path::Relative('Core', 'Data', 'Static', 'RootMode.css'));
    }

    /**
    * @arguments [%2:string]
    */
    function OnImg($file)
    {
        Response::SendFile(Path::Relative('Core', 'Data', 'Static', 'img', $file));
    }

    /**
    * @arguments [%2:int=1]
    */
    function OnContinuousIntegration($page)
    {
        \Core\Internal\Tools::Assert(\Core\Config::Instance()->HaveItem('jenkins'), 'Не найдена секция Jenkins');
        $limit = 5;

        $this->LayoutData()->sector = 'continuous-integration';
        $info = Jenkins::Instance()->GetInformation();

        switch($info['color'])
        {
            case 'anime':
                $info['bootstrap-color'] = 'alert-info';
                $info['statusText'] = 'Запущен процесс сборки';
                break;
            case 'red':
                $info['bootstrap-color'] = 'alert-danger';
                $info['statusText'] = 'Поcледняя сборка провалилась';
                break;
            case 'blue':
                $info['bootstrap-color'] = 'alert-success';
                $info['statusText'] = 'Поcледняя сборка успешно прошла все тесты';
                break;
            case 'aborted':
                $info['bootstrap-color'] = 'alert-error';
                $info['statusText'] = 'Сборка была прервана';
                break;
            case 'disabled':
                $info['bootstrap-color'] = '';
                $info['statusText'] = 'Проект закрыт.';
                break;

        }

        $totalPages = max( ceil( count ( $info['builds'] ) / $limit ), 1 );
        $page = min($totalPages, max($page, 1));
        $start = ($page-1) * $limit;
        $end = $page*$limit-1;

        $builds = array();
        $i = -1;
        foreach($info['builds'] as $build)
        {
            $i++;
            if($i < $start)
                continue;
            if($i > $end)
                break;

            $builds[] = Jenkins::Instance()->GetBuildShortInfo($build['number']);
        }
        $this->Result()->info = $info;
        $this->Result()->builds = $builds;
        $this->Result()->page = $page;
        $this->Result()->totalPages = $totalPages;
    }

    /**
    * @arguments [%2:int]
    */
    function OnBuild($buildId)
    {
        $this->LayoutData()->sector = 'continuous-integration';
        $build = Jenkins::Instance()->GetBuildInfo($buildId);
        $tests = Jenkins::Instance()->GetTests($buildId);
        $this->Result()->build = $build;
        $this->Result()->tests = $tests;
    }

    function OnStartBuild()
    {
        $id = \Core\Internal\Jenkins\Jenkins::Instance()->StartBuild();
        \Core\Response::Redirect(":build/".$id);
    }

    private function GetHash($pwd)
    {
        $h = Utils::MakePasswordHash($pwd);
        return md5($h . User::Instance()->IP());
    }

    private function GenerateHelpersView()
    {
        $helpersResponse = array();
        $helpers = Internal\Tools::GetModules("Helpers");
        foreach($helpers as $helper)
        {
            $help = new \ReflectionClass($helper);
            $refMethods = $help->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC);
            $helperMethods = array();
            foreach($refMethods as $refMethod)
            {
                /**
                *
                * @var \ReflectionMethod
                */
                $refMethod;
                if($refMethod->getDeclaringClass()->getName() != $helper)
                    continue;
                $params = $refMethod->getParameters();
                $brackets = 0;
                $pVal = "";
                foreach($params as $param)
                {

                    /**
                    * @var \ReflectionParameter
                    */
                    $param;

                    if($param->isOptional())
                    {
                        $brackets++;
                        $pVal .= '[';
                    }
                    if($param->getClass() != null)
                        $pVal.='\\'. $param->getClass() . ' ';

                    $pVal .= '$'. $param->GetName();

                    if($param->isDefaultValueAvailable())
                        $pVal .= ' = \'' . $param->getDefaultValue() .'\'';

                    $pVal .= ', ';
                }
                $pVal = \Core\Utils::SubstringLeft($pVal, mb_strlen($pVal)-2);
                while($brackets-->0)
                    $pVal .= ']';

                $helperMethods[$refMethod->GetName()] = $pVal;
            }

            $helperView = $this->CreateView('Helper');
            $helperView->name = $helper;
            $helperView->methods = $helperMethods;

            $helpersResponse[] = $helperView->ToHtml();
        }

        $this->Result()->helpers = $helpersResponse;
    }
}

?>
