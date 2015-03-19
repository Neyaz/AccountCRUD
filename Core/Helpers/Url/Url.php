<?

/**
 * Url
 */
class Url extends Core\Helper
{
    /**
     * Относительный URL, начинающийся с входящей в текущий роутинг части пути<br/>
     * URL строиться начиная с Request::Instance()->PathInRoute()
     * @param string $path Путь который будет прописан после роутинговой части
     * @return string URL
     */
    public static function FromRoute($path)
    {
        if (\Core\Utils::SubstringLeft($path, 1) != '/')
            $path = '/' . $path;
        return \Core\Request::Instance()->PathInRoute() . $path;
    }

    /**
     * Формирование ссылки с поддержкой SmartURL
     * @param string $path
     * @return string
     */
    public static function Make($path)
    {
        if (\Core\Utils::SubstringLeft($path, 1) == ':')
            return self::FromRoute(\Core\Utils::SubstringMid($path, 1));
        else
            return $path;
    }
}

?>
