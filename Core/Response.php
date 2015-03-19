<?

namespace Core;

/**
 * Управление ответом
 */
class Response
{
    /**
     * Вывести пользователю ошибку 404.
     * ВНИМАНИЕ! Вызов этого метода прерывает дальнейшее выполнение кода приложения!
     * @param string $DebugErrorText Вспомогательный комментарий, виден только при включенной отладке
     */
    static function Error404($DebugErrorText = '')
    {
        self::Error(404, $DebugErrorText);
    }

    /**
     * Вывести пользователю ошибку 403.<br/>
     * ВНИМАНИЕ! Вызов этого метода прерывает дальнейшее выполнение кода приложения!
     * @param string $DebugErrorText Вспомогательный комментарий, виден только при включенной отладке
     */
    static function Error403($DebugErrorText = '')
    {
        self::Error(403, $DebugErrorText);
    }

    /**
     * Вывести пользователю ошибку 500.<br/>
     * ВНИМАНИЕ! Вызов этого метода прерывает дальнейшее выполнение кода приложения!
     * @param string $DebugErrorText Вспомогательный комментарий, виден только при включенной отладке
     */
    static function Error500($DebugErrorText = '')
    {
        self::Error(500, $DebugErrorText);
    }

    /**
     * Вывести пользователю ошибку.<br/>
     * ВНИМАНИЕ! Вызов этого метода прерывает дальнейшее выполнение кода приложения!
     * @param string $DebugErrorText Вспомогательный комментарий, виден только при включенной отладке
     */
    static function Error($code, $DebugErrorText = '')
    {
        self::ClearOutputBuffers();
        if ($DebugErrorText != null)
            Internal\DebugManager::SetErrorText ($DebugErrorText);

        if ($code == 400)
            header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
        else if ($code == 401)
            header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
        else if ($code == 403)
            header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
        else if ($code == 404)
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        else if ($code == 500)
            header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');
        else
            header($_SERVER['SERVER_PROTOCOL'].' '.$code);

        if ($DebugErrorText != '' && Config::Instance()->IsDebug())
        {
            echo $DebugErrorText;
            die;
        }

        $err = new Internal\ErrorRoute($code);
        LiteWork::Instance()->SetRouteConfig($err);
        LiteWork::Instance()->Execute();

        die;
    }

    /**
     * Переадрисовать пользователя на адрес, начальная часть пути для которого определяется автоматичеси.<br/>
     * ВНИМАНИЕ! Вызов этого метода прерывает дальнейшее выполнение кода приложения!
     * @see Подробнее - см. SmartURL
     * @param string $path Путь внутри роутинга\абсолютный
     */
    static function Redirect($path)
    {
        $url = \Url::Make($path);

        self::ClearOutputBuffers();
        header("location: $url");
        die;
    }

    /**
     * Передать пользователю файл.<br/>
     * ВНИМАНИЕ! Вызов этого метода прерывает дальнейшее выполнение кода приложения!
     */
    static function SendFile($file, $fileName = null)
    {
        self::ClearOutputBuffers();

        if($fileName == '')
        {
            $pathInfo = pathinfo($file);
            $fileName = $path["basename"];
        }

        set_time_limit(0);
        // Handle If-Modified-Since
        $fileDate=filemtime($file);
        $ifModifiedSince= isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
        if($ifModifiedSince && strtotime($ifModifiedSince) >= $filedate) {
            header('HTTP/1.0 304 Not Modified');
            die;
        }
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $fileDate).' GMT');
        header('Content-Length: '.filesize($file));
        $type = Internal\MimeType::GetFileType($file);
        header("Content-Type: $type");
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        // Output file

        $handle=fopen($file,'r');
        fpassthru($handle);
        fclose($handle);
        die;
    }

    /**
     * Очистить output буферы PHP
     */
    public static function ClearOutputBuffers()
    {
        while (ob_get_level())
            ob_end_clean();
    }
}

?>
