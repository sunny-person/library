<?php


namespace App\Repository;


use App\Entity\TypePh;
use Doctrine\ORM\EntityRepository;

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

}