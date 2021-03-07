<?php


namespace App\Repository;


use App\Entity\Author;
use App\Entity\Books;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\PublishingHouse;
use App\Entity\TypePh;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityRepository;

class BooksRepository extends EntityRepository {

    public function getBooks(?int $categories = null, int $offset = 0, int $limit = 10) : array {
        $books = array();
        $dbBooks = array();

        if (!isset($categories) || empty($categories)) {
            $dbBooks = $this->getAllBooks($offset, $limit);
        } else {
            $dbBooks = $this->getBooksByCategory($categories);
        }

        if (empty($dbBooks)) {
            return $books;
        }

        foreach ($dbBooks as $dbBook) {
            $book = new Books();
            $book->setIdBooks($dbBook['id_books']);
            $book->setTitle($dbBook['title']);
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

            $books[] = $book;
        }

        return $books;
    }

    private function getAllBooks(int $offset = 0, int $limit = 10) : array {
        $connection = $this->getEntityManager()->getConnection();
        $query =
            "SELECT * FROM books
            INNER JOIN category on category.id_category = books.parent
            INNER JOIN author on author.id_author = books.id_author
            INNER JOIN type_ph on type_ph.id_type_ph = books.id_type_ph
            INNER JOIN publishing_house on publishing_house.id_publishing_house = books.id_publishing_house
            INNER JOIN city on city.id_city = books.id_city
            ORDER BY title LIMIT ?, ?
            ";

        $statement = $connection->prepare($query);
        $statement->bindValue(1, $offset, ParameterType::INTEGER);
        $statement->bindValue(2, $limit, ParameterType::INTEGER);
        $statement->execute();

        $result = $statement->fetchAllAssociative();

        return $result;
    }

    /**
     * @param int $category
     * @return Books[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getBooksByCategory(int $category): array {
        $query = "SELECT * FROM books
            INNER JOIN category on category.id_category = books.parent
            INNER JOIN author on author.id_author = books.id_author
            INNER JOIN type_ph on type_ph.id_type_ph = books.id_type_ph
            INNER JOIN publishing_house on publishing_house.id_publishing_house = books.id_publishing_house
            INNER JOIN city on city.id_city = books.id_city
            WHERE books.parent = :cat OR category.parent = :cat
            ORDER BY title";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('cat', $category);
        $statement->execute();

        return $statement->fetchAllAssociative();
    }

    public function getBook(int $id): ?Books {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT * FROM books
            INNER JOIN category on category.id_category = books.parent
            INNER JOIN author on author.id_author = books.id_author
            INNER JOIN type_ph on type_ph.id_type_ph = books.id_type_ph
            INNER JOIN publishing_house on publishing_house.id_publishing_house = books.id_publishing_house
            INNER JOIN city on city.id_city = books.id_city
            WHERE id_books = ?
            ORDER BY title";
        $statement = $connection->prepare($query);
        $statement->bindValue(1, $id);
        $statement->execute();

        if ($statement->rowCount() < 1){
            return null;
        }

        $dbBook = $statement->fetchAssociative();

        $book = new Books();
        $book->setIdBooks($dbBook['id_books']);
        $book->setTitle($dbBook['title']);
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

    /**
     * @param Books $book
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function addBook(Books $book): bool{
        $query = "INSERT INTO books SET
                 `title` = ?,
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
        $statement->bindValue(2, $book->getAuthor());
        $statement->bindValue(3, $book->getUrl());
        $statement->bindValue(4, $book->getPage());
        $statement->bindValue(5, $book->getYear());
        $statement->bindValue(6, $book->getPublishingHouse());
        $statement->bindValue(7, $book->getTypePh());
        $statement->bindValue(8, $book->getCity());
        $statement->bindValue(9, $book->getParent());

        return $statement->execute();
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
        $statement->bindValue(2, $book->getAuthor()->getIdAuthor());
        $statement->bindValue(3, $book->getUrl());
        $statement->bindValue(4, $book->getPage());
        $statement->bindValue(5, $book->getYear());
        $statement->bindValue(6, $book->getPublishingHouse()->getIdPublishingHouse());
        $statement->bindValue(7, $book->getTypePh()->getIdTypePh());
        $statement->bindValue(8, $book->getCity()->getIdCity());
        $statement->bindValue(9, $book->getParent()->getIdCategory());
        $statement->bindValue(10, $book->getIdBooks());

        return $statement->execute();
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
        return $statement->execute();
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
        $statement->execute();

        return (int) $statement->fetchOne();
    }

    private function getBooksCountByCategory(int $category): int {
        $query = "SELECT COUNT(*) FROM books
            INNER JOIN category on category.id_category = books.parent
            WHERE books.parent = :cat OR category.parent = :cat
            ORDER BY title";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('cat', $category);
        $statement->execute();

        return (int) $statement->fetchOne();
    }

}