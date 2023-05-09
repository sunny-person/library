<?php


namespace App\Repository;


use App\Entity\Category;
use App\Repository\Exceptions\CategoryRepositoryException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class CategoryRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    private const NO_PARENT_ID = 0;

    /**
     * @return Category[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCategories() : array {
        $query = 'SELECT * FROM category';
        $connection = $this->getEntityManager()->getConnection();

        $statement = $connection->prepare($query);
        $result = $statement->executeQuery();

        $result = $result->fetchAllAssociative();

        $categories = array();
        foreach ($result as $dbCategory) {
            $category = new Category();
            $category->setNameCategory($dbCategory['name_category']);
            $category->setIdCategory($dbCategory['id_category']);
            $category->setParent($dbCategory['parent']);

            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * @param int $categoryId
     * @return Category[]
     */
    public function getCategoriesChain(int $categoryId): array {
        $query = 'select c1.name_category as c1Name, c1.id_category as c1ID,
                    c2.name_category as c2Name, c2.id_category as c2ID
                    from category as c1
                    left join category as c2 on c1.parent = c2.id_category
                    where c1.id_category = ?';
        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $categoryId);
        $result = $statement->execute();

        if ($result->rowCount() < 1) {
            return array(
                'child' => null,
                'parent' => null
            );
        }

        $dbCategories = $result->fetchAssociative();

        $parentId = $dbCategories['c2ID'] ?? 0;

        $child = new Category();
        $child->setNameCategory($dbCategories['c1Name']);
        $child->setIdCategory($dbCategories['c1ID']);
        $child->setParent($dbCategories['c2ID']);

        if ($parentId === 0) {
            return array(
                'child' => $child,
                'parent' => null
            );
        }

        $parent = new Category();
        $parent->setNameCategory($dbCategories['c2Name']);
        $parent->setIdCategory($dbCategories['c2ID']);
        $parent->setParent(self::NO_PARENT_ID);

        return array(
            'child' => $child,
            'parent' => $parent
        );
    }

    /**
     * @param Category $category
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function addCategory(Category $category): bool {
        $query = "INSERT INTO category SET
                              `name_category` = ?,
                              `parent` = ? ";
        $statement=$this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $category->getNameCategory());
        $statement->bindValue(2, $category->getParent());

        return (bool) $statement->executeStatement();
    }

    public function getCategory(int $categoryId): Category {
        $query = "SELECT * FROM category WHERE id_category = ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $categoryId);
        $result = $statement->executeQuery();

        if ($result->rowCount() < 1) {
            throw new \InvalidArgumentException('Category with such id not found!', 0);
        }

        $dbCategory = $result->fetchAssociative();

        $category = new Category();
        $category->setIdCategory($dbCategory['id_category']);
        $category->setNameCategory($dbCategory['name_category']);
        $category->setParent($dbCategory['parent']);

        return $category;
    }

    /**
     * @param Category $category
     * @return bool
     * @throws CategoryRepositoryException
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateCategory(Category $category): bool {
        $query = "UPDATE category SET name_category = :name, parent = :parent WHERE id_category = :id";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue('name', $category->getNameCategory());
        $statement->bindValue('parent', $category->getParent());
        $statement->bindValue('id', $category->getIdCategory());

        try {
            return (bool) $statement->executeStatement();
        } catch (Exception $e) {
            $errorMessage = 'Не удалось обновить категорию.';
            throw new CategoryRepositoryException($errorMessage, 0, $e);
        }
    }

    /**
     * @param Category $category
     * @return bool
     * @throws CategoryRepositoryException
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteCategory(Category $category): bool {
        $query = "DELETE FROM category WHERE id_category = ?";

        $statement = $this->getEntityManager()->getConnection()->prepare($query);
        $statement->bindValue(1, $category->getIdCategory());

        try {
            return (bool) $statement->executeStatement();
        } catch (Exception $e) {
            $errorMessage = 'Не удалось обновить категорию.';
            throw new CategoryRepositoryException($errorMessage, 1, $e);
        }
    }

}