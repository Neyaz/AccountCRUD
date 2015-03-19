<?php

namespace Core\Internal\Doctrine;


class Profiler implements \Doctrine\DBAL\Logging\SQLLogger
{
    public $start = null;
	public $currentQuery;

    private $data;

    public function __construct()
    {
        $this->data = array();
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->start = microtime(true);
        $this->currentQuery = $sql;
        $this->currentParams = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function StopQuery()
    {
		$this->data[] = array("query"=>$this->currentQuery, "params"=>$this->currentParams, "time"=>(microtime(true) - $this->start));
		$this->start = null;
        $this->currentQuery = null;
    }

    public function PrintInfo()
    {
        return $this->data;
    }

}