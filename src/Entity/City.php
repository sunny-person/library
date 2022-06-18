<?php


namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity(repositoryClass="App\Repository\CityRepository")
 * @ORM\Table(name="city")
 */
class City
{
    /**
     * @Id
     * @Column("id_city")
     * @GeneratedValue
     */
    private $idCity;
    /**
     * @Column(name="city", type="string")
     */
    private $city;

    /**
     * @return mixed
     */
    public function getIdCity()
    {
        return $this->idCity;
    }

    /**
     * @param mixed $idCity
     */
    public function setIdCity($idCity): void
    {
        $this->idCity = $idCity;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

}