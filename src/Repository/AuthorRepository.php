<?php


namespace App\Repository;


use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

class AuthorRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    /**
     * @return Author[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAuthors(): array {
        $query = 'SELECT * FROM author';

        $statement = $this->getEntityManager()->getConnection()->executeQuery($query);

        $dbAuthors = $statement->fetchAllAssociative();

        $authors = array();

        foreach ($dbAuthors as $dbAuthor) {
            $author = new Author();
            $author->setIdAuthor($dbAuthor['id_author']);
            $author->setNameAuthor($dbAuthor['name_author']);

            $authors[] = $author;
        }

        return $authors;
    }

    public function getAuthor(int $id): Author {
        $query = "SELECT * FROM author WHERE `id_author` = ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $id, ParameterType::INTEGER);
        $result = $statement->executeQuery();

        if ($result->rowCount() < 1) {
            throw new InvalidArgumentException('Author not found!', 0);
        }

        $dbAuthor = $result->fetchAssociative();

        $author = new Author();
        $author->setIdAuthor($dbAuthor['id_author']);
        $author->setNameAuthor($dbAuthor['name_author']);

        return $author;
    }

    /**
     * @param Author $author
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function addAuthor(Author $author): bool {
        $query = "INSERT INTO author SET `name_author`= ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $author->getNameAuthor());

        $result = $statement->executeStatement();
        return (bool) $result;
    }

    /**
     * @param Author $author
     * @return bool
     */
    public function updateAuthor(Author $author): bool {
        $query = "UPDATE author SET `name_author` = ? WHERE `id_author` = ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $author->getNameAuthor());
        $statement->bindValue(2, $author->getIdAuthor());

        $result = $statement->executeStatement();
        return (bool) $result;
    }

    public function deleteAuthor(Author $author): bool {
        $query = "DELETE FROM author WHERE `id_author` = ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $author->getIdAuthor());

        $result = $statement->executeStatement();
        return (bool) $result;
    }
}