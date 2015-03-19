<?

use \Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="accounts")
 */
class Account extends \Core\BaseEntity
{
    /**
     * @Id @Column(type="integer") @GeneratedValue
     */
    private $id;

    /**
     * @Column(type="string", length=30, name="login", nullable=false)
     */
    private $login;

    /**
     * @Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @Column(name="email", type="string", length=50, nullable=true)
     */
    private $email;

    /**
     * @Column(name="gender", type="string", length=6, nullable=true)
     */
    private $gender;

    /**
     * @Column(name="birthday", type="datetime", nullable=true)
     */
    private $birthday;

    /**
     * @Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * Get id
     * @return integer 
     */
    public function GetId()
    {
        return $this->id;
    }

    /**
     * Get login
     * @return string 
     */
    public function GetLogin()
    {
        return $this->login;
    }
    /**
     * Set login
     * @param string $login
     */
    public function SetLogin($login)
    {
        $this->login = $login;
    }

    /**
     * Get name
     * @return string
     */
    public function GetName()
    {
        return $this->name;
    }
    /**
     * Set name
     * @param string $name
     */
    public function SetName($name)
    {
        $this->name = $name;
    }

    /**
     * Get email
     * @return string
     */
    public function GetEmail()
    {
        return $this->email;
    }
    /**
     * Set email
     * @param string $email
     */
    public function SetEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get gender
     * @return string
     */
    public function GetGender()
    {
        return $this->gender;
    }
    /**
     * Set gender
     * @param string $gender
     */
    public function SetGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * Get birthday
     * @return string
     */
    public function GetBirthday()
    {
        return $this->birthday;
    }
    /**
     * Set birthday
     * @param string $birthday
     */
    public function SetBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * Get comment
     * @return string
     */
    public function GetComment()
    {
        return $this->comment;
    }
    /**
     * Set comment
     * @param string $comment
     */
    public function SetComment($comment)
    {
        $this->comment = $comment;
    }
}