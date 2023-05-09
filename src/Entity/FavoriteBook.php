<?php


namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity(repositoryClass="App\Repository\FavoriteBooksRepository")
 * @ORM\Table(name="favorite_book")
 */
class FavoriteBook {
    /**
     * @var integer $userId
     * @Id
     * @Column(name="user_id", type="integer")
     */
    private $userId;
    /**
     * @var integer $bookId
     * @Id
     * @Column(name="book_id", type="integer")
     */
    private $bookId;

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