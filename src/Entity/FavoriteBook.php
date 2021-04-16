<?php


namespace App\Entity;


use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity(repositoryClass="App\Repository\FavoriteBooksRepository")
 */
class FavoriteBook {

    /**
     * @Id
     * @Column("id")
     * @GeneratedValue
     */
    private $id;

    private $userId;

    private $bookId;

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getBookId() {
        return $this->bookId;
    }

    /**
     * @param mixed $bookId
     */
    public function setBookId($bookId): void {
        $this->bookId = $bookId;
    }

}