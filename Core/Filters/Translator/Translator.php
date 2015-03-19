<?

namespace Core;

class Translator extends Filter
{

    private $domain;
                                               //php gettext Plural-Forms: nplurals
    function BeforeExecute()
    {
        $locale = \Core\User::Instance()->GetLocale();

        putenv('LC_ALL=' . $locale);
        setlocale(LC_ALL, $locale);

        $this->domain = $this->GetConfigItem('domain', 'default');

        bindtextdomain($this->domain, \Core\Path::Relative('Locales'));
        bindtextdomain('Common', \Core\Path::Relative('Locales'));

        bind_textdomain_codeset($this->domain, 'UTF-8');
        bind_textdomain_codeset('Common', 'UTF-8');

        textdomain($this->domain);

        return TRUE;
    }

    function AfterExecute()
    {
        parent::AfterExecute();
        return TRUE;
    }

    function BeforeResponse()
    {
        return TRUE;
    }


}

?>
