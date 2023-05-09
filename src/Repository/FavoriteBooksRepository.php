<?php


namespace App\Repository;


use App\Entity\FavoriteBook;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FavoriteBooksRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FavoriteBook::class);
    }

    public function addEntry(FavoriteBook $fb): bool {
        $query = 'INSERT INTO favorite_book SET user_id = :u, book_id = :b';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('u', $fb->getUserId());
        $statement->bindValue('b', $fb->getBookId());

        return (bool) $statement->executeStatement();
    }

    public function deleteEntry(FavoriteBook $fb): bool {
        $query = 'DELETE FROM favorite_book WHERE user_id = :u and book_id = :b';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('u', $fb->getUserId());
        $statement->bindValue('b', $fb->getBookId());

        return (bool) $statement->executeStatement();
    }

    /**
     * @param int $userId
     * @return FavoriteBook[]
     */
    public function getUserEntries(int $userId): array {
        $query = 'SELECT * FROM favorite_book WHERE user_id = ?';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $userId);
        $result = $statement->executeQuery();

        $dbEntries = $result->fetchAllAssociative();

        $entries = array();
        foreach ($dbEntries as $dbEntry) {
            $fb = new FavoriteBook();
            $fb->setUserId($dbEntry['user_id']);
            $fb->setBookId($dbEntry['book_id']);

            $entries[] = $fb;
        }

        return $entries;
    }

}