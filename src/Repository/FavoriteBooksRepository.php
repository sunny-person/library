<?php


namespace App\Repository;


use App\Entity\FavoriteBook;
use Doctrine\ORM\EntityRepository;

class FavoriteBooksRepository extends EntityRepository {

    public function addEntry(FavoriteBook $fb): bool {
        $query = 'INSERT INTO favorite_book SET user_id = :u, book_id = :b';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('u', $fb->getUserId());
        $statement->bindValue('b', $fb->getBookId());

        return $statement->execute();
    }

    public function deleteEntry(FavoriteBook $fb): bool {
        $query = 'DELETE FROM favorite_book WHERE user_id = :u and book_id = :b';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('u', $fb->getUserId());
        $statement->bindValue('b', $fb->getBookId());

        return $statement->execute();
    }

    /**
     * @param int $userId
     * @return FavoriteBook[]
     */
    public function getUserEntries(int $userId): array {
        $query = 'SELECT * FROM favorite_book WHERE user_id = ?';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $userId);
        $statement->execute();

        $dbEntries = $statement->fetchAllAssociative();

        $entries = array();
        foreach ($dbEntries as $dbEntry) {
            $fb = new FavoriteBook();
            $fb->setId($dbEntry['id']);
            $fb->setUserId($dbEntry['user_id']);
            $fb->setBookId($dbEntry['book_id']);

            $entries[] = $fb;
        }

        return $entries;
    }

}