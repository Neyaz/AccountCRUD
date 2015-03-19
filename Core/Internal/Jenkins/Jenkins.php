<?php
namespace Core\Internal\Jenkins;
class Jenkins
{
    static private $instance = NULL;

    private $domain;
    private $port;
    private $job;
    private $jobInfo;

    /**
     * Синглтон
     * @return Jenkins
     */
    static function Instance()
    {
        if (self::$instance == NULL)
                self::$instance = new Jenkins();
        return self::$instance;
    }

    private function __construct()
    {
        $this->domain = \Core\Config::Instance()->GetStr('jenkins/domain');
        $this->job = \Core\Config::Instance()->GetStr('jenkins/job');
        $this->port = \Core\Config::Instance()->GetStr('jenkins/port');
    }

    public static function CreateJob($host, $port, $job)
    {
        if( $curl = curl_init() )
        {
            curl_setopt($curl, CURLOPT_URL, 'http://' . $host . ':' . $port . '/createItem');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Connection: close'));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, 'name=' . $job . '&mode=copy&from=php_template');
            curl_exec($curl);
            curl_close($curl);
        }

    }

    public function GetBaseUrl()
    {
        return 'http://' . $this->domain . ':' . $this->port . '/job/' . $this->job ;
    }

    private function SendRequest($method, $params = '' , $isPost = false, $isBaseUrl = true)
    {
        $url = $this->GetBaseUrl() . ($method != '' ? '/' : '') . $method . '/api/json';
        if($isPost && $params != '')
            $url .= '?' . $params;
        $out = null;

        //echo $url;

        if( $curl = curl_init() )
        {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Connection: close'));

            if($isPost)
            {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            }

            $out = curl_exec($curl);
            curl_close($curl);
        }

        return $out;
    }

    public function GetInformation()
    {
        $data = $this->SendRequest('');
        return json_decode($data, true);
    }

    public function GetBuildShortInfo($buildNo)
    {
        $data = $this->SendRequest($buildNo, 'tree=result,building,number,timestamp,actions[causes[userName]],description');
        $data = json_decode($data, true);

        $this->PreccessBuildInfo($data);

        return $data;
    }

    public function GetBuildInfo($buildNo)
    {
        $data = $this->SendRequest($buildNo);
        $data =  json_decode($data, true);

        $this->PreccessBuildInfo($data);

        foreach($data['changeSet']['items'] as &$itemLog)
        {
            $time = new \DateTime();
            $time->setTimestamp(\Core\Utils::SubstringLeft($itemLog['timestamp'], mb_strlen($itemLog['timestamp'])-3));
            $itemLog['time'] = $time;
        }

        return $data;
    }
    public function GetTests($buildNo)
    {
        $data = $this->SendRequest($buildNo.'/testReport');
        $data =  json_decode($data, true);
        return $data;
    }

    public function StartBuild()
    {
        $info = $this->GetInformation();
        $this->SendRequest('build', '', true);
        sleep(5);
        return $info['nextBuildNumber'];
    }

    private function PreccessBuildInfo(&$data)
    {
        foreach($data['actions'] as $item)
        {
            if(isset($item['causes'][0]['userName']))
            {
                $data['user'] = $item['causes'][0]['userName'];
                unset($data['actions']);
                break;
            }
        }
        $time = new \DateTime();
        $time->setTimestamp(\Core\Utils::SubstringLeft($data['timestamp'], mb_strlen($data['timestamp'])-3));
        $data['time'] = $time;

    }
}
?>
