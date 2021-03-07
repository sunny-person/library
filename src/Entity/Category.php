<?php


namespace App\Entity;


use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;


/**
 * @Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{

    /**
     * @Id
     * @Column("`id_category`")
     * @GeneratedValue
     */
    private $idCategory;
    private $nameCategory;
    private $parent;

    /**
     * @return mixed
     */
    public function getIdCategory()
    {
        return $this->idCategory;
    }

    /**
     * @param mixed $idCategory
     */
    public function setIdCategory($idCategory): void
    {
        $this->idCategory = $idCategory;
    }

    /**
     * @return mixed
     */
    public function getNameCategory()
    {
        return $this->nameCategory;
    }

    /**
     * @param mixed $nameCategory
     */
    public function setNameCategory($nameCategory): void
    {
        $this->nameCategory = $nameCategory;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent): void
    {
        $this->parent = $parent;
    }
}