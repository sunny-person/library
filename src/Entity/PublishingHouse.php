<?php


namespace App\Entity;


use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity(repositoryClass="App\Repository\PublishingHouseRepository")
 */
class PublishingHouse
{

    /**
     * @Id
     * @Column("id_publishing_house")
     * @GeneratedValue
     */
    private $idPublishingHouse;
    private $namePublishingHouse;

    /**
     * @return mixed
     */
    public function getIdPublishingHouse()
    {
        return $this->idPublishingHouse;
    }

    /**
     * @param mixed $idPublishingHouse
     */
    public function setIdPublishingHouse($idPublishingHouse): void
    {
        $this->idPublishingHouse = $idPublishingHouse;
    }

    /**
     * @return mixed
     */
    public function getNamePublishingHouse()
    {
        return $this->namePublishingHouse;
    }

    /**
     * @param mixed $namePublishingHouse
     */
    public function setNamePublishingHouse($namePublishingHouse): void
    {
        $this->namePublishingHouse = $namePublishingHouse;
    }

}