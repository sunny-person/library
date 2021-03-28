<?php


namespace App\Entity;


use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity(repositoryClass="App\Repository\BooksRepository")
 */
class Books
{

    /**
     * @Id
     * @Column("`id_books`")
     * @GeneratedValue
     */
    private $idBooks;
    private $title;
    private $description;
    private $author;
    private $url;
    private $page;
    private $parent;
    private $publishingHouse;
    private $typePh;
    private $city;
    private $year;

    /**
     * @return mixed
     */
    public function getIdBooks()
    {
        return $this->idBooks;
    }

    /**
     * @param mixed $idBooks
     */
    public function setIdBooks($idBooks): void
    {
        $this->idBooks = $idBooks;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author): void
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page): void
    {
        $this->page = $page;
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

    /**
     * @return mixed
     */
    public function getPublishingHouse()
    {
        return $this->publishingHouse;
    }

    /**
     * @param mixed $publishingHouse
     */
    public function setPublishingHouse($publishingHouse): void
    {
        $this->publishingHouse = $publishingHouse;
    }

    /**
     * @return mixed
     */
    public function getTypePh()
    {
        return $this->typePh;
    }

    /**
     * @param mixed $typePh
     */
    public function setTypePh($typePh): void
    {
        $this->typePh = $typePh;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year): void
    {
        $this->year = $year;
    }

}