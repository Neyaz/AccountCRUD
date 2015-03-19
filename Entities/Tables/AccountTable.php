<?php

class AccountTable extends \Core\BaseTable
{
    static function GetLast()
    {
        return self::Database()->CreateQuery('select a from Account a where a.id = (select MAX(k.id) from Account k)')->GetOneOrNullResult();
        // Альтернатива ;)
        //return self::Database()->CreateQuery('select a from Account a ORDER BY a.id DESC')->SetMaxResults(1)->GetOneOrNullResult();
    }

    static function TotalCount()
    {
        return self::Database()->CreateQuery('select COUNT(a.id) from Account a')->GetSingleScalarResult();
    }
}