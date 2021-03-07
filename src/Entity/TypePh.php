<?php


namespace App\Entity;


use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity(repositoryClass="App\Repository\PhTypesRepository")
 */
class TypePh
{

    /**
     * @Id
     * @Column("id_type_ph")
     * @GeneratedValue
     */
    private $idTypePh;
    private $nameTypePh;

    /**
     * @return mixed
     */
    public function getIdTypePh()
    {
        return $this->idTypePh;
    }

    /**
     * @param mixed $idTypePh
     */
    public function setIdTypePh($idTypePh): void
    {
        $this->idTypePh = $idTypePh;
    }

    /**
     * @return mixed
     */
    public function getNameTypePh()
    {
        return $this->nameTypePh;
    }

    /**
     * @param mixed $nameTypePh
     */
    public function setNameTypePh($nameTypePh): void
    {
        $this->nameTypePh = $nameTypePh;
    }

}