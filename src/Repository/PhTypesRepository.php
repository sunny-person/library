<?php


namespace App\Repository;


use App\Entity\TypePh;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;


class PhTypesRepository extends EntityRepository {

    /**
     * @return TypePh[]
     */
    public function getPhTypes(): array {
        $query = 'SELECT * FROM type_ph';

        $statement = $this->getEntityManager()->getConnection()->executeQuery($query);

        $dbPhTypes = $statement->fetchAllAssociative();

        $phTypes = array();
        foreach ($dbPhTypes as $dbPhType) {
            $phType = new TypePh();
            $phType->setNameTypePh($dbPhType['name_type_ph']);
            $phType->setIdTypePh($dbPhType['id_type_ph']);

            $phTypes[] = $phType;
        }

        return $phTypes;
    }

    public function addTypePh(TypePh $typePh): bool {
        $query = 'INSERT INTO type_ph SET name_type_ph = :name';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('name', $typePh->getNameTypePh());

        return (bool) $statement->executeStatement();
    }

    public function getPHType(int $id): TypePh {
        $query = "SELECT * FROM type_ph WHERE `id_type_ph` = ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $id);
        $result = $statement->execute();

        if ($result->rowCount() < 1) {
            throw new InvalidArgumentException('TypePH not found!', 0);
        }

        $dbTypePh = $result->fetchAssociative();

        $typePh = new TypePh();
        $typePh->setIdTypePh($dbTypePh['id_type_ph']);
        $typePh->setNameTypePh($dbTypePh['name_type_ph']);

        return $typePh;
    }


    public function updateTypePh(TypePh $typePh): bool {
        $query = 'UPDATE type_ph SET name_type_ph = :name WHERE id_type_ph = :id';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('name', $typePh->getNameTypePh());
        $statement->bindValue('id', $typePh->getIdTypePh());

        return (bool) $statement->executeStatement();
    }

    public function deleteTypePh(TypePh $typePh): bool {
        $query = 'DELETE FROM type_ph WHERE id_type_ph = ?';

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $typePh->getIdTypePh());

        return (bool) $statement->executeStatement();
    }
}