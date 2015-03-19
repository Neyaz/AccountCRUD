<?

namespace Core\Internal;

/**
 * DebugManager
 */
class DebugManager
{
    private static $viewData = array();
    private static $debugData = '';
    private static $errorData = '';

    /**
     * Добавить отладочные данные о въюшке
     */
    static function AddView($file, $arr)
    {
        $path = pathinfo($file);
        $dir = \Core\Path::ExtractRelativeSubPath($path['dirname'], \Core\Path::BasePath());
        $name = '[' . $path['filename'] . ']';

        if (!isset(self::$viewData[$dir][$name]))
        {
            $work = array();
            self::$viewData[$dir][$name] = &$work;
        }
        else
        {
            $idx = array_keys(self::$viewData[$dir][$name]);
            if (count($idx) > 5)
            {
                self::$viewData[$dir][$name][] = "...";
                return;
            }
            if (!is_int(end($idx)))
            {
                $old = self::$viewData[$dir][$name];
                self::$viewData[$dir][$name] = array();
                self::$viewData[$dir][$name][] = $old;
            }
            $work = array();
            self::$viewData[$dir][$name][] = &$work;
        }
        foreach ($arr as $key => $val)
        {
            $val = self::Process($val);

            $work[$key] = $val;
        }
    }

    static function DumpFilters($filters)
    {
        if (count($filters) > 0)
        {
            $filtersInfo = array();
            foreach ($filters as $it)
            {
                $mod = get_class($it->Module());
                $name = get_class($it);
                $filtersInfo[$mod][$name] = $it->GetConfigArray();
            }
            \Core\DebugPrint($filtersInfo, 'Filters', 'Фильтры загруженные для текущего роутинга');
        }
    }

    /**
     * Вывести отладочные данные
     */
    static function DebugDump()
    {
        if (self::$errorData != null)
            \Core\DebugPrint(self::$errorData, '<span style="color: red">ERROR</span>', 'Информация о причинах возникновения ошибки');

        \Core\DebugPrint(self::$viewData, 'Module views', 'Переменные переданные всеми модулями во въюшки');
        \Core\DebugPrint(\Core\Internal\Runtime::Instance()->InternalGetLayoutData()->GetItems(), 'LayoutData', 'Данные переданные в Layout');

        $reqObj = \Core\Request::Instance();
        $request = array();
        $request['Path'] = $reqObj->FullPath();
        $request['PathInRoute'] = $reqObj->PathInRoute();
        $request['PathAfterRoute'] = $reqObj->PathAfterRoute();
        if ($reqObj->Data()->HaveItems())
            $request['Data'] = $reqObj->Data()->ItemsArray();
        $request['User data'] = \Core\User::Instance()->ItemsArray();
        $request['Cookies data'] = \Core\User::Instance()->Cookies()->ItemsArray();
        \Core\DebugPrint($request, 'Request', 'Запрос');
    }

    static function PrintData($data, $level = 0)
    {
        //$spaces = str_repeat('&nbsp;', $level * 2);
        echo '<ul style="border-left: solid #eaeaea 1px; color: #333; font-size: 12px; padding-left: 14px; line-height: 16px; margin-left: 0; list-style-type: square;">';
        if (is_array($data))
        {
            $vec = \Core\ArrayTools::IsVector($data);
            foreach ($data as $key => $val)
            {
                if ($vec)
                    echo '<li style="list-style-type: circle;">';
                else
                    echo '<li>';
                if (is_array($val))
                {
                    if (!$vec) echo "<span style='color: #ad071a'>$key:</span>";
                    if (count($val) != 0)
                        echo self::PrintData($val, $level + 1);
                    else
                        echo " <span style='color: #128492; font-weight: bold;'>{empty}</span>";
                }
                else
                {
                    if (!$vec) echo "<span style='color: #285f97; padding-right: 4px;'>$key:</span> ";
                    echo self::ConvertPrintableValue($val);
                }
            }
        }
        else
        {
            echo $data;
            echo '<br>';
        }
        echo '</ul>';
    }

    private static function ConvertPrintableValue($val)
    {
        $specStyle = 'color: #128492; font-weight: bold;';
        if ($val === NULL)
            return "<span style='$specStyle'>null</span>";
        if (is_int($val))
            return "<span style='color: #078e20;'>$val</span>";
        if (is_bool($val))
            return "<span style='$specStyle'>" . ($val ? 'true' : 'false') . "</span>";

        $val = htmlspecialchars($val);
        $val = str_replace("\n", '<span style="color: #779cb8">\n</span>', $val);
        return $val;
    }

    /**
     * Вывести отладочные данные пользователю
     */
    static function OutputWriteDebugInfo()
    {
        $route = \Core\LiteWork::Instance()->CurrentRoute();
        if (get_class($route) == 'Core\Internal\RootModeRoute')
        {
            $link = '/';
            $name = 'вернуться на сайт';
        }
        else
        {
            $link = '/rootmode';
            $name = 'управление';
        }
        echo '<div style="clear: both; margin-top: 30px;" id="debugLiteWork">';
        echo self::$debugData;
        echo '<a href="' . $link . '" style="float: right; font-size: 10px; margin-right: 5px; color: #ccc;">' . $name . '</a><br/>';
        echo '<a href=\'#\' onclick="document.getElementById(\'debugLiteWork\').style.display=\'none\'; return false; " style="float: right; font-size: 10px; margin-right: 5px; color: #ccc;">скрыть отладку</a>';
        echo '</div>';
    }

    static function AddDebugText($text)
    {
        self::$debugData .= $text;
    }

    static function SetErrorText($text)
    {
        self::$errorData .= $text;
    }

    /**
     * Не вызывать! Вызывается автоматически из DebugPrint
     * @param type $data
     * @return type
     */
    static function Process($data)
    {
        $processed = \Core\Utils::ExpandObjects($data, 7, 'DEBUG');
        if (is_array($data))
        {
            foreach ($processed as $key => $value)
            {
                if (is_string($value))
                    $processed[$key] = self::SimplifyString($value);
            }
        }
        return $processed;
    }

    private static function SimplifyString($data, $maxSize = 200)
    {
        if (is_object($data))
            return 'Object';

        if (is_null($data))
            return 'null';

        if (is_array($data))
            return 'Array()';

        $str = strval($data);
        $str = str_replace("\r", '', $str);

        while (strpos($str, '  ')!==false)
            $str = str_replace('  ', ' ', $str);
        while (strpos($str, "\t\t")!==false)
            $str = str_replace("\t\t", "\t", $str);

        // trim
        if (mb_strlen($str) > $maxSize) $str = mb_substr($str, 0, $maxSize).'...';



        if ($str=='') $str = '<span style="color: #128492;">""</span>';

        return $str;
    }
}

?>
