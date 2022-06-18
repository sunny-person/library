<?php


namespace App\Repository;

use App\Entity\PublishingHouse;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;


class PublishingHouseRepository extends EntityRepository {

    /**
     * @return PublishingHouse[]
     */
    public function getPublishingHouses(): array {
        $query = 'SELECT * FROM publishing_house';

        $statement = $this->getEntityManager()->getConnection()->executeQuery($query);

        $dbPhs = $statement->fetchAllAssociative();

        $phs = array();
        foreach ($dbPhs as $dbPh) {
            $ph = new PublishingHouse();
            $ph->setNamePublishingHouse($dbPh['name_publishing_house']);
            $ph->setIdPublishingHouse($dbPh['id_publishing_house']);

            $phs[] = $ph;
        }

        return $phs;
    }

    /**
     * @param PublishingHouse $publishingHouse
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function addPublishingHouse(PublishingHouse $publishingHouse): bool{
        $query = "INSERT INTO publishing_house SET `name_publishing_house` = ?";

        $statement=$this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $publishingHouse->getNamePublishingHouse());

        return (bool) $statement->executeStatement();
    }

    public function getPublishingHouse($publishingHouseId): PublishingHouse{
        $query = "SELECT * FROM publishing_house WHERE id_publishing_house = ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $publishingHouseId);
        $result = $statement->execute();

        if ($result->rowCount() < 1) {
            throw new InvalidArgumentException('Publishing House not found!', 0);
        }

        $dbPublishingHouse = $result->fetchAssociative();

        $publishing_house = new PublishingHouse();
        $publishing_house->setIdPublishingHouse($dbPublishingHouse['id_publishing_house']);
        $publishing_house->setNamePublishingHouse($dbPublishingHouse['name_publishing_house']);

        return $publishing_house;
    }

    public function updatePublishingHouse(PublishingHouse $publishingHouse): bool {
        $query = 'UPDATE publishing_house SET name_publishing_house = :name WHERE id_publishing_house = :id';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('name', $publishingHouse->getNamePublishingHouse());
        $statement->bindValue('id', $publishingHouse->getIdPublishingHouse());

        return (bool) $statement->executeStatement();
    }

    public function deletePublishingHouse(PublishingHouse $publishingHouse): bool {
        $query = 'DELETE FROM publishing_house WHERE id_publishing_house = ?';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $publishingHouse->getIdPublishingHouse());

        return (bool) $statement->executeStatement();
    }

}