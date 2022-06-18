<?php


namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;


class Roles
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id_role")
     * @ORM\GeneratedValue()
     */
    private $idRole;
    /**
     * @ORM\Column(name="name", type="string")
     */
    private $name;

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

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
}