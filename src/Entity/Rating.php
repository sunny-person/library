<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity()
 * @ORM\Table(name="rating")
 */
class Rating {
    /**
     * @ORM\Id()
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue()
     */
    private ?int $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id_users")
     * @Assert\NotNull()
     */
    private ?Users $user;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Books", inversedBy="ratings")
     * @ORM\JoinColumn(name="book_id", referencedColumnName="id_books")
     * @Assert\NotNull()
     */
    private ?Books $book;
    /**
     * @ORM\Column(name="value", type="integer")
     * @Assert\NotNull()
     */
    private ?int $value;
    /**
     * @ORM\Column(name="comment", type="string")
     * @Assert\NotNull()
     */
    private ?string $comment;

    public function getId(): ?int {
        return $this->id;
    }

    public function getUser(): ?Users {
        return $this->user;
    }

    public function getBook(): ?Books {
        return $this->book;
    }

    public function getValue(): ?int {
        return $this->value;
    }

    public function getComment(): ?string {
        return $this->comment;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setUser(?Users $user): void {
        $this->user = $user;
    }

    public function setBook(?Books $book): void {
        $this->book = $book;
    }

    public function setValue(?int $value): void {
        $this->value = $value;
    }

    public function setComment(?string $comment): void {
        $this->comment = $comment;
    }
}