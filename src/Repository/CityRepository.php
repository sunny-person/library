<?php


namespace App\Repository;


use App\Entity\City;
use Doctrine\ORM\EntityRepository;

class CityRepository extends EntityRepository {

    /**
     * @return City[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCities(): array {
        $query = 'SELECT * FROM city';

        $statement = $this->getEntityManager()->getConnection()->executeQuery($query);

        $dbCities = $statement->fetchAllAssociative();

        $cities = array();
        foreach ($dbCities as $dbCity) {
            $city = new City();
            $city->setIdCity($dbCity['id_city']);
            $city->setCity($dbCity['city']);

            $cities[] = $city;
        }

        return $cities;
    }

    /**
     * @param City $city
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function addCity(City $city): bool{
        $query = "INSERT INTO city SET `city` = ?";
        $statement=$this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $city->getCity());

        return $statement->execute();
    }

    public function getCity(int $cityId): City {
        $query = "SELECT * FROM city WHERE id_city = ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $cityId);
        $statement->execute();

        if ($statement->rowCount() < 1) {
            throw new \InvalidArgumentException('City with such id not found!');
        }

        $dbCity = $statement->fetchAssociative();

        $city = new City();
        $city->setCity($dbCity['city']);
        $city->setIdCity($dbCity['id_city']);

        return $city;
    }

    public function updateCity(City $city): bool {
        $query = "UPDATE city SET city = :city WHERE id_city = :id";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('city', $city->getCity());
        $statement->bindValue('id', $city->getIdCity());

        return $statement->execute();
    }

    public function deleteCity(City $city): bool {
        $query = "DELETE FROM city WHERE id_city = ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $city->getIdCity());

        return $statement->execute();
    }
}