<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LanguageRepository")
 */
class Language
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $abbreviation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $logo;

    /**
     * @ORM\Column(type="string", length=3)
     */
    protected $direction;

	public function __toString()
	{
		return $this->title;
	}
	
	public function getId()
	{
		return $this->id;
	}

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function setDirection($direction)
    {
        $this->direction = $direction;
    }
}