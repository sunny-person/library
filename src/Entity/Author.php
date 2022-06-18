<?php


namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Column;

/**
 * @Entity(repositoryClass="App\Repository\AuthorRepository")
 * @ORM\Table(name="author")
 */
class Author {

    /**
     * @Id
     * @Column("id_author")
     * @GeneratedValue
     */
    private $idAuthor;
    /**
     * @Column(name="name_author", type="string")
     */
    private $nameAuthor;

    /**
     * @return mixed
     */
    public function getIdAuthor()
    {
        return $this->idAuthor;
    }

    /**
     * @param mixed $idAuthor
     */
    public function setIdAuthor($idAuthor): void
    {
        $this->idAuthor = $idAuthor;
    }

    /**
     * @return mixed
     */
    public function getNameAuthor()
    {
        return $this->nameAuthor;
    }

    /**
     * @param mixed $nameAuthor
     */
    public function setNameAuthor($nameAuthor): void
    {
        $this->nameAuthor = $nameAuthor;
    }

}