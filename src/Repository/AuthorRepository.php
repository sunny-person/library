<?php


namespace App\Repository;


use App\Entity\Author;
use Doctrine\ORM\EntityRepository;

class AuthorRepository extends EntityRepository {

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

        return $statement->execute();
    }

}