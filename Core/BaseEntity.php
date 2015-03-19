<?

namespace Core;

/**
 * Entity
 *
 */
class BaseEntity
{
    public function Database()
    {
        return \Core\Database::Main();
    }

    public function Persist()
    {
        $this->Database()->Persist($this);
    }

    public function Remove()
    {
        $this->Database()->Remove($this);
    }

    public function ToArray($resultPresentation = NULL)
    {
        $result = array();
        $r = new \ReflectionClass($this);
        foreach ($r->getProperties() as $it)
        {
            $it->setAccessible(true);
            $result[$it->getName()] = $it->getValue($this);
        }
        return $result;
    }
}

?>
