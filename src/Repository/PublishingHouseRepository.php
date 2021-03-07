<?php


namespace App\Repository;


use App\Entity\PublishingHouse;
use Doctrine\ORM\EntityRepository;

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

        return $statement->execute();
    }

}