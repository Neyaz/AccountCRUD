<?php

namespace Core
{
    $path = __DIR__ . DIRECTORY_SEPARATOR;
    require_once $path.'Enumerations.php';
    require_once $path.'Path.php';
    require_once $path.'Exceptions.php';
    require_once $path.'Request.php';
    require_once $path.'Config.php';
    require_once $path.'Application.php';
    require_once $path.'Route.php';
    require_once $path.'Controller.php';
    require_once 'Internal'.DIRECTORY_SEPARATOR.'Tools.php';
    require_once $path.'External'.DIRECTORY_SEPARATOR.'SymfonyConfig.php';
    require_once $path.'Response.php';

    /**
     * Вывести информацию для отладки
     * (она будет отображаться только если включена отладка)
     */
    function DebugPrint($value, $message = null, $comment = null)
    {
        if (!Config::Instance()->IsDebug())
            return false;

        $value = Internal\DebugManager::Process($value);
        ob_start();
        static $num = 0;
        $id = 'DebugMsg'.$num;
        $table = 'Table'.$id;
        $num++;
        $js = "if (document.getElementById('$id').style.display!='none'){
            document.getElementById('$id').style.display='none'
            document.getElementById('$table').style.margin = '1px';
            document.getElementById('$table').style.marginLeft = '4px';
        } else {
            document.getElementById('$id').style.display='inline';
            document.getElementById('$table').style.margin = '5px';
        }";
        echo "\n";
        echo '<table id="'.$table.'" style="text-align: left; margin: 1px; width: 90%; border: none; margin-left: 4px; ">';
        if ($message == null) $message = 'Debug';
        echo "<tr><th onclick=\"".$js."\" style='background-color: #c9efff; -moz-border-radius: 5px; border-radius: 5px; padding: 1px 12px; text-align: center; text-shadow: #8ccfeb 1px 1px 1px;'>
        <a href='#' onclick='return false' style='color: #09506e; text-decoration: none;'>$message</a></th></tr>";
        echo "<tr><td><div id='$id' style='display: none;'>";
        echo '<pre style="font-size: 11px;">';

        if ($comment != null)
            echo "<p style='font-size: 14px; padding: 0; margin: 10px 0;'>$comment:</p>";
        else
            echo '<br/>';

        $begin = ob_get_contents();
        ob_end_clean();
        ob_start();

        Internal\DebugManager::PrintData($value);

        echo '</pre>';
        echo '</div></td></tr>';
        echo "</table>\n";
        $data = ob_get_contents();
        ob_end_clean();

        \Core\Internal\DebugManager::AddDebugText($begin.$data);
    }

    class RootModeApplication extends Application
    {
    }

    \Core\Internal\Tools::Initialize();
}

namespace
{
    if(!function_exists('_n'))
    {
        function _n($s1, $s2, $s3)
        {
            return ngettext($s1, $s2, $s3) ;
        }
    }

    if(!function_exists('_d'))
    {
        function _d($s1, $s2)
        {
            return dgettext($s1, $s2) ;
        }
    }

    if(!function_exists('_dn'))
    {
        function _dn($s1, $s2, $s3, $s4)
        {
            return dngettext($s1, $s2, $s3, $s4) ;
        }
    }


    if(!function_exists('_c'))
    {
        function _c($s1)
        {
            return _d('Common', $s1) ;
        }
    }

    if(!function_exists('_cn'))
    {
        function _cn($s1, $s2, $s3)
        {
            return _dn('Common', $s1, $s2, $s3) ;
        }
    }
}

