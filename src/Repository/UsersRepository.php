<?php


namespace App\Repository;

use App\Entity\Users;
use Doctrine\ORM\EntityRepository;


class UsersRepository extends EntityRepository {
    /**
     * @param string $login
     * @param string $password
     * @return null|Users
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getUser(string $login, string $password) : ?Users {
        $query = "SELECT * FROM `users` WHERE `login` = '$login' AND `password` = '$password' LIMIT 1";
        $connection = $this->getEntityManager()->getConnection();

        $statement = $connection->prepare($query);
        $statement->execute();

        if ($statement->rowCount() < 1) {
            return null;
        }

        $dbUser = $statement->fetchAssociative();

        $user = new Users();
        $user->setIdUsers($dbUser['id_users']);
        $user->setFullName($dbUser['full_name']);
        $user->setEmail($dbUser['email']);
        $user->setIdRole($dbUser['id_role']);
        $user->setLogin($dbUser['login']);
        $user->setPassword($dbUser['password']);

        return $user;
    }

    public function addUser(string $fullName, string $login, string $email, string $password) : bool {
        $query = "INSERT INTO `users` (`id_users`, `full_name`, `login`, `email`, `password`, `id_role`) 
                    VALUES (NULL, :fullName, :login, :email, :password, '2');";

        $connection = $this->getEntityManager()->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindValue("fullName", $fullName);
        $statement->bindValue("login", $login);
        $statement->bindValue("email", $email);
        $statement->bindValue("password", $password);

        return $statement->execute();
    }

}