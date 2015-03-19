<?

namespace Core;
                     
class DQLGenerator
{
    public static function Select($table = "", $wheres = array(), $joins = array(), $orderBy = '', $customAliases = null, $group = "", $having = '')
    {
        Internal\Tools::Assert($table != "", "Поле таблицы не должно быть пустым!");
        $tableParts = explode(" ", $table);
        $aliases = array(end($tableParts));
        $joinString = "";
        if(count($joins))
        {
            foreach($joins as $join)
            {
                $exploder = explode(" ", $join);
                $aliases[] = $exploder[1];
                $joinString .= "LEFT JOIN " . $join . " ";
            }
        }


        $whereString = count($wheres) ? "WHERE (".implode(') AND (', $wheres).")" : "";
        $groupBy = ($group) ? " GROUP BY " . $group : '';
        $orderBy = ($orderBy) ? " ORDER BY " . $orderBy : '';
        $having = ($having) ? " HAVING " . $having : '';
        return "SELECT " . ((!$customAliases) ? implode(", ", $aliases) : $customAliases) . 
                        " FROM " . $table . " " . $joinString . " " . $whereString . $groupBy . $having . $orderBy;
    }

    public static function GetRef($typeEntity, $value)
    {
        return Database::Main()->GetReference($typeEntity, $value);
    }

    public static function GetWhereInPartQuery($field = null, $data = null, &$params = array(), $namespace = null)
    {
        if(!$data)
            return "0 = 1";
        $res = $field . " IN (";

        if($namespace == null)
            $namespace = Utils::GeneratePassword (8, false, true, false);

        $i=0;
        $parts = array();
        foreach($data as $item)
        {
            $parts[]= ":" . $namespace . "_" . $i;
            $params[$namespace."_".$i] = $item;
            $i++;
        }

        $res .= implode(", ", $parts) . ")";

        return $res;
    }
}

?>
