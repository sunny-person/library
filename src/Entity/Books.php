<?php


namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * @Entity(repositoryClass="App\Repository\BooksRepository")
 * @ORM\Table(name="books")
 */
class Books
{
    /**
     * @Id
     * @Column("`id_books`")
     * @GeneratedValue
     */
    private $idBooks;
    /**
     * @Column(name="title", type="string")
     */
    private $title;
    /**
     * @Column(name="description", type="text")
     */
    private $description;
    /**
     * Many Users have Many Groups.
     * @ManyToMany(targetEntity="App\Entity\Author")
     * @JoinTable(name="books_author",
     *      joinColumns={@JoinColumn(name="id_books", referencedColumnName="id_books")},
     *      inverseJoinColumns={@JoinColumn(name="id_author", referencedColumnName="id_author")}
     *      )
     */
    private $author;
    /**
     * @Column(name="URL", type="string")
     */
    private $url;
    /**
     * @Column(name="page", type="integer")
     */
    private $page;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id_category")
     */
    private $parent;
    /**
     * Many Users have Many Groups.
     * @ManyToMany(targetEntity="App\Entity\PublishingHouse")
     * @JoinTable(name="books_publishing_house",
     *      joinColumns={@JoinColumn(name="id_books", referencedColumnName="id_books")},
     *      inverseJoinColumns={@JoinColumn(name="id_publishing_house", referencedColumnName="id_publishing_house")}
     *      )
     */
    private $publishingHouse;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TypePh")
     * @ORM\JoinColumn(name="id_type_ph", referencedColumnName="id_type_ph")
     */
    private $typePh;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumn(name="id_city", referencedColumnName="id_city")
     */
    private $city;
    /**
     * @Column(name="year", type="integer")
     */
    private $year;

    /**
     * @OneToMany(targetEntity="App\Entity\Rating", mappedBy="book")
     */
    private $ratings;

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

    public function getAverageRating(): ?int {
        if (!isset($this->ratings) || $this->ratings->count() <= 0) {
            return 0;
        }
        $ratingSum = array_reduce($this->ratings->toArray(), fn ($c, $r) => $c + $r->getValue());
        return intdiv($ratingSum, $this->ratings->count());
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