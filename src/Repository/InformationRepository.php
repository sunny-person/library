<?php


namespace App\Repository;


use App\Entity\Information;
use Doctrine\ORM\EntityRepository;

class InformationRepository extends EntityRepository {
    public function getInformation(int $userId, int $bookId): ?Information{

        $query="SELECT * FROM `information` WHERE `user`= ? and `book`= ?";
        $connection=$this->getEntityManager()->getConnection();
        $statement=$connection->prepare($query);
        $statement->bindValue(1, $userId);
        $statement->bindValue(2, $bookId);
        $result = $statement->executeQuery();

        if($result->rowCount() < 1) {
            return null;
        }
        $dbInformation = $result->fetchAssociative();

        $information = new Information();
        $information->setId($dbInformation['id']);
        $information->setPage($dbInformation['page']);
        $information->setBook($dbInformation['book']);
        $information->setUser($dbInformation['user']);
        $information->setDate($dbInformation['date']);

        return $information;
    }

    public function addInformation(Information $information): bool{
        $query="INSERT INTO `information` (`id`, `user`, `book`, `page`, `date`) VALUES (NULL, ?, ?, ?, ?)";

        $connection=$this->getEntityManager()->getConnection();
        $statement=$connection->prepare($query);

        $statement->bindValue(1, $information->getUser());
        $statement->bindValue(2, $information->getBook());
        $statement->bindValue(3, $information->getPage());
        $statement->bindValue(4, $information->getDate());

        return (bool) $statement->executeStatement();
    }

    public function updateInformation(Information $information): bool{
        $query="UPDATE `information` SET `page`= ?, `date`= ? WHERE id = ?";

        $connection=$this->getEntityManager()->getConnection();
        $statement=$connection->prepare($query);

        $statement->bindValue(1, $information->getPage());
        $statement->bindValue(2, $information->getDate());
        $statement->bindValue(3, $information->getId());

        return (bool) $statement->executeStatement();
    }

}