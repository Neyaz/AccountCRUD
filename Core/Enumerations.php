<?

namespace Core\Enum;

class PresentationType
{
    /**
     * View, подставляемый в Layout
     */
    const VIEW = 1;
    /**
     * Данные закодированные в JSON формат
     */
    const JSON = 2;
    /**
     * Представление в виде текстовых даннных
     */
    const TEXT = 3;
    /**
     * Запретить доступ
     */
    const DENY = 4;
}

class DomainNameMode
{
    /**
     * Домен в том виде в котором он запрошен пользователем<br/><br/>
     * @example www.site.com -> www.site.com<br/>
     * site.com -> site.com
     */
    const ORIGINAL = 1;
    /**
     * Если в адресе есть WWW он будет удален<br/><br/>
     * @example www.site.com -> site.com<br/>
     * site.com -> site.com
     */
    const WITHOUT_WWW = 2;
    /**
     * Добавить WWW если он отсутствует<br/><br/>
     * @example www.site.com -> www.site.com<br/>
     * site.com -> www.site.com
     */
    const FORCE_WWW = 3;
    /**
     * Получить доменное имя второго уровня<br/><br/>
     * @example project.subdomain.site.com -> site.com<br/>
     * site.com -> site.com
     */
    const SECOND_LEVEL = 4;
}
