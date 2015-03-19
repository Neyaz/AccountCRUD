<?

namespace Core\Internal;

/**
 * AnnotationManager
 *
 */
class AnnotationManager
{
    static private $instance = NULL;
    /**
     * Синглетон
     * @return AnnotationManager
     */
    static function Instance()
    {
        if (self::$instance == NULL)
            self::$instance = new AnnotationManager();
        return self::$instance;
    }
    private function __construct()
    {
    }
    private function __clone()
    {
    }

    private $loadedClasses = array();
    private $parsedMethods = array();
    private $parsedClasses = array();

    private function GetAnnotationParserObject($className)
    {
        if (!isset($this->loadedClasses[$className]))
           $this->loadedClasses[$className] = new AnnotationParser($className);

        $parser = $this->loadedClasses[$className];
        return $parser;
    }

    private function GetClassAnnotations($className)
    {
        if (!isset($this->parsedClasses[$className]))
        {
            $parser = $this->GetAnnotationParserObject($className);
            $ann = $parser->GetClassAnnotations();
            $this->parsedClasses[$className] = $ann;
        }

        return $this->parsedClasses[$className];
    }

    private function GetMethodAnnotations($className, $method)
    {
        if (!isset($this->parsedMethods[$className]))
            $this->parsedMethods[$className] = array();

        $cur = &$this->parsedMethods[$className][$method];
        if (!isset($cur))
        {
            $parser = $this->GetAnnotationParserObject($className);
            $ann = $parser->GetForMethod($method);
            $cur = $ann;
        }

        return $this->parsedMethods[$className][$method];
    }

    private function GetAnnotationData($params, $typeName, $default = false)
    {
        if (!isset($params[$typeName]))
            return $default;

        $arr = $params[$typeName];
        return $arr;
    }

    /**
     * Слить анотации фильтруя их по заданному типу
     * @param array $result Результирующий массив. Стартовое значение должно быть <b>FALSE</b>
     * @param array $annotations Аннтоатия текущего элемента
     * @param string $typeName Тип данных для выборки
     * @return type
     */
    private function MergeAnnotationsByType(&$result, $annotations, $typeName)
    {
        if ($result !== false && !is_array($result))
            return false; // Этот тип не допускает множественных значений


        if ($annotations == false) return false;
        if (!array_key_exists($typeName, $annotations)) return false;

        $selected = $annotations[$typeName];
        if (is_array($selected))
        {
            if ($result === false) $result = array();

            foreach ($selected as $key => $value)
            {
                if (isset($result[$key])) continue; // Без перезаписи
                $result[$key] = $value;
            }
        }
        else
            $result = $selected;

        return true;
    }

    public function GetAnnotationsByType($action, $moduleName, $applicationName, $typeName)
    {
        $result = false;

        $method = \Core\Controller::GetMethodNameFromAction($action);
        $appClass = \Core\Application::GetApplicationClassName($applicationName);

        $params = $this->GetMethodAnnotations($moduleName, $method);
        $this->MergeAnnotationsByType($result, $params, $typeName);

        $params = \Core\LiteWork::Instance()->CurrentRoute()->ItemsArray();
        $this->MergeAnnotationsByType($result, $params, $typeName);

        $params = $this->GetClassAnnotations($moduleName);
        $this->MergeAnnotationsByType($result, $params, $typeName);

        $params = $this->GetClassAnnotations($appClass);
        $this->MergeAnnotationsByType($result, $params, $typeName);

        return $result;
    }

    public function GetResultPresentationName($action, $moduleName, $applicationName, $requestType)
    {
       $method = \Core\Controller::GetMethodNameFromAction($action);

       $presentation = $this->GetAnnotationsByType($action, $moduleName, $applicationName, $requestType);

       if ($presentation != NULL)
           return $presentation;

       if ($requestType == 'default')
           return \Core\Utils::DashedToCamelCase($action);

       return NULL;
    }

    public function GetFilters($action, $moduleName, $applicationName)
    {
        return $this->GetAnnotationsByType($action, $moduleName, $applicationName, 'filter');
    }

    public function GetLayout($action, $moduleName, $applicationName)
    {
        $rt = $this->GetAnnotationsByType($action, $moduleName, $applicationName, 'layout');
        if ($rt === NULL) $rt = 'null';
        return $rt;
    }

    public function GetArgumentsInfo($action, $moduleName, $applicationName)
    {
        $method = \Core\Controller::GetMethodNameFromAction($action);
        $appClass = \Core\Application::GetApplicationClassName($applicationName);

        $params = $this->GetMethodAnnotations($moduleName, $method);

        if ($params == NULL)
           return false;

        if (!key_exists('arguments', $params))
           return false;

       return $params['arguments'];
    }
}