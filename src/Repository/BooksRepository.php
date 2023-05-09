<?php


namespace App\Repository;


use App\Entity\Author;
use App\Entity\Books;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\PublishingHouse;
use App\Entity\TypePh;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;

class BooksRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Books::class);
    }

    public function getBooks(?int $categories = null, int $offset = 0, int $limit = 10) : array {
        $books = array();

        if (!isset($categories) || empty($categories)) {
            $dbBooks = $this->getAllBooks($offset, $limit);
        } else {
            $dbBooks = $this->getBooksByCategory($categories);
        }

        if (empty($dbBooks)) {
            return $books;
        }

        foreach ($dbBooks as $dbBook) {
            $books[] = $this->createBook($dbBook);
        }

        return $books;
    }

    private function getAllBooks(int $offset = 0, int $limit = 10) : array {
        $connection = $this->getEntityManager()->getConnection();
        $query =
            "SELECT * FROM books
            LEFT JOIN category on category.id_category = books.parent
            LEFT JOIN author on author.id_author = books.id_author
            LEFT JOIN type_ph on type_ph.id_type_ph = books.id_type_ph
            LEFT JOIN publishing_house on publishing_house.id_publishing_house = books.id_publishing_house
            LEFT JOIN city on city.id_city = books.id_city
            LEFT JOIN (select avg(`value`) as avg, book_id from rating group by book_id) r on r.book_id = books.id_books
            ORDER BY title LIMIT ?, ?
            ";

        $statement = $connection->prepare($query);
        $statement->bindValue(1, $offset, ParameterType::INTEGER);
        $statement->bindValue(2, $limit, ParameterType::INTEGER);
        $result = $statement->executeQuery();

        return $result->fetchAllAssociative();
    }

    /**
     * @param int $category
     * @return Books[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getBooksByCategory(int $category): array {
        $query = "SELECT * FROM books
            LEFT JOIN category on category.id_category = books.parent
            LEFT JOIN author on author.id_author = books.id_author
            LEFT JOIN type_ph on type_ph.id_type_ph = books.id_type_ph
            LEFT JOIN publishing_house on publishing_house.id_publishing_house = books.id_publishing_house
            LEFT JOIN city on city.id_city = books.id_city
            LEFT JOIN (select avg(`value`) as avg, book_id from rating group by book_id) r on r.book_id = books.id_books
            WHERE books.parent = :cat OR category.parent = :cat
            ORDER BY title";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('cat', $category);
        $result = $statement->execute();

        return $result->fetchAllAssociative();
    }

    public function getBook(int $id): ?Books {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT * FROM books
            LEFT JOIN category on category.id_category = books.parent
            LEFT JOIN author on author.id_author = books.id_author
            LEFT JOIN type_ph on type_ph.id_type_ph = books.id_type_ph
            LEFT JOIN publishing_house on publishing_house.id_publishing_house = books.id_publishing_house
            LEFT JOIN city on city.id_city = books.id_city
            WHERE id_books = ?
            ORDER BY title";
        $statement = $connection->prepare($query);
        $statement->bindValue(1, $id);
        $result = $statement->executeQuery();

        if ($result->rowCount() < 1){
            return null;
        }

        $dbBook = $result->fetchAssociative();
        return $this->createBook($dbBook);
    }

    /**
     * @param Books $book
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function addBook(Books $book): bool{
        $query = "INSERT INTO books SET
                 `title` = ?,
                 `description` = ?,
                 `id_author` = ?,
                 `URL` = ?,
                 `page` = ?,
                 `year` = ?,
                 `id_publishing_house` = ?,
                 `id_type_PH` = ?,
                 `id_city` = ?,
                 `parent` = ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $book->getTitle());
        $statement->bindValue(2, $book->getDescription());
        $statement->bindValue(3, $book->getAuthor());
        $statement->bindValue(4, $book->getUrl());
        $statement->bindValue(5, $book->getPage());
        $statement->bindValue(6, $book->getYear());
        $statement->bindValue(7, $book->getPublishingHouse());
        $statement->bindValue(8, $book->getTypePh());
        $statement->bindValue(9, $book->getCity());
        $statement->bindValue(10, $book->getParent());

        return (bool) $statement->executeStatement();
    }

    /**
     * @param Books $book
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateBook(Books $book): bool {
        $query = "UPDATE books SET
                 `title` = ?,
                 `description` = ?,
                 `id_author` = ?,
                 `URL` = ?,
                 `page` = ?,
                 `year` = ?,
                 `id_publishing_house` = ?,
                 `id_type_PH` = ?,
                 `id_city` = ?,
                 `parent` = ?
                 WHERE id_books = ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $book->getTitle());
        $statement->bindValue(2, $book->getDescription());
        $statement->bindValue(3, $book->getAuthor()->getIdAuthor());
        $statement->bindValue(4, $book->getUrl());
        $statement->bindValue(5, $book->getPage());
        $statement->bindValue(6, $book->getYear());
        $statement->bindValue(7, $book->getPublishingHouse()->getIdPublishingHouse());
        $statement->bindValue(8, $book->getTypePh()->getIdTypePh());
        $statement->bindValue(9, $book->getCity()->getIdCity());
        $statement->bindValue(10, $book->getParent()->getIdCategory());
        $statement->bindValue(11, $book->getIdBooks());

        return (bool) $statement->executeStatement();
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteBook(int $id): bool {
        $query = 'DELETE FROM books WHERE id_books = ?';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $id);
        return (bool) $statement->executeStatement();
    }

    public function getBooksCount(?int $category = null): int {
        if (!isset($category)) {
            return $this->getAllBooksCount();
        }

        return $this->getBooksCountByCategory($category);
    }

    private function getAllBooksCount(): int {
        $query = 'SELECT COUNT(*) FROM books';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $result = $statement->execute();

        return (int) $result->fetchOne();
    }

    private function getBooksCountByCategory(int $category): int {
        $query = "SELECT COUNT(*) FROM books
            INNER JOIN category on category.id_category = books.parent
            WHERE books.parent = :cat OR category.parent = :cat
            ORDER BY title";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('cat', $category);
        $result = $statement->executeQuery();

        return (int) $result->fetchOne();
    }

    /**
     * @param string $find
     * @return Books[]
     */
    public function searchBooks(string $find): array {
        $query = "SELECT * FROM books
            LEFT JOIN category on category.id_category = books.parent
            LEFT JOIN author on author.id_author = books.id_author
            LEFT JOIN type_ph on type_ph.id_type_ph = books.id_type_ph
            LEFT JOIN publishing_house on publishing_house.id_publishing_house = books.id_publishing_house
            LEFT JOIN city on city.id_city = books.id_city
            WHERE books.title LIKE :q OR author.name_author LIKE :q OR books.description LIKE :q
            ORDER BY books.title LIMIT 30";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('q', "%$find%");
        $result = $statement->execute();

        $dbBooks = $result->fetchAllAssociative();
        $books = array();
        foreach ($dbBooks as $dbBook) {
            $books[] = $this->createBook($dbBook);
        }

        return $books;
    }

    public function getUserFavoriteBooks(int $userId, int $limit = 10, int $offset = 0): array {
        $query = 'SELECT * FROM books
                  LEFT JOIN category on category.id_category = books.parent
                  LEFT JOIN author on author.id_author = books.id_author
                  LEFT JOIN type_ph on type_ph.id_type_ph = books.id_type_ph
                  LEFT JOIN publishing_house on publishing_house.id_publishing_house = books.id_publishing_house
                  LEFT JOIN city on city.id_city = books.id_city
                  JOIN favorite_book fb on fb.book_id = books.id_books
                  WHERE fb.user_id = :userId
                  LIMIT :offset, :limit';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('userId', $userId);
        $statement->bindValue('offset', $offset, ParameterType::INTEGER);
        $statement->bindValue('limit', $limit, ParameterType::INTEGER);
        $result = $statement->execute();

        $dbBooks = $result->fetchAllAssociative();
        $books = array();
        foreach ($dbBooks as $dbBook) {
            $books[] = $this->createBook($dbBook);
        }

        return $books;
    }

    public function getUserFavoriteBooksCount(int $userId): int {
        $query = 'SELECT COUNT(*) FROM books b
                  JOIN favorite_book fb ON fb.book_id = b.id_books
                  WHERE fb.user_id = ?';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $userId);
        $result = $statement->execute();

        return (int) $result->fetchOne();
    }

    private function createBook(array $dbBook): Books {
        $book = new Books();
        $book->setIdBooks($dbBook['id_books']);
        $book->setTitle($dbBook['title']);
        $book->setDescription($dbBook['description']);
        $book->setUrl($dbBook['URL']);
        $book->setPage($dbBook['page']);
        $book->setYear($dbBook['year']);

        $author = new Author();
        $author->setIdAuthor($dbBook['id_author']);
        $author->setNameAuthor($dbBook['name_author']);
        $book->setAuthor($author);

        $parent = new Category();
        $parent->setIdCategory($dbBook['id_category']);
        $parent->setParent($dbBook['parent']);
        $parent->setNameCategory($dbBook['name_category']);
        $book->setParent($parent);

        $publishingHouse = new PublishingHouse();
        $publishingHouse->setIdPublishingHouse($dbBook['id_publishing_house']);
        $publishingHouse->setNamePublishingHouse($dbBook['name_publishing_house']);
        $book->setPublishingHouse($publishingHouse);

        $typePh = new TypePh();
        $typePh->setIdTypePh($dbBook['id_type_PH']);
        $typePh->setNameTypePh($dbBook['name_type_ph']);
        $book->setTypePh($typePh);

        $city = new City();
        $city->setIdCity($dbBook['id_city']);
        $city->setCity($dbBook['city']);
        $book->setCity($city);

        return $book;
    }

}