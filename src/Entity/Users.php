<?php


namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity(repositoryClass="App\Repository\UsersRepository")
 */
class Users
{
    /**
     * @Id
     * @Column("id_users")
     * @GeneratedValue
     */
    private $idUsers;
    /**
     * @Column(name="full_name", type="string")
     */
    private $fullName;
    /**
     * @Column(name="login", type="string")
     */
    private $login;
    /**
     * @Column(name="email", type="string")
     */
    private $email;
    /**
     * @Column(name="password", type="string")
     */
    private $password;
    /**
     * @Column(name="id_role", type="integer")
     */
    private $idRole;

    /**
     * @return mixed
     */
    public function getIdUsers()
    {
        return $this->idUsers;
    }

    /**
     * @param mixed $idUsers
     */
    public function setIdUsers($idUsers): void
    {
        $this->idUsers = $idUsers;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param mixed $fullName
     */
    public function setFullName($fullName): void
    {
        $this->fullName = $fullName;
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login): void
    {
        $this->login = $login;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getIdRole()
    {
        return $this->idRole;
    }

    /**
     * @param mixed $idRole
     */
    public function setIdRole($idRole): void
    {
        $this->idRole = $idRole;
    }
}