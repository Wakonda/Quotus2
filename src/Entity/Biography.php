<?php

namespace App\Entity;

use App\Service\GenericFunction;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BiographyRepository")
 */
class Biography
{
	const AUTHOR = "author";
	const FICTIONAL_CHARACTER = "fictionalCharacter";
	
	const AUTHOR_CANONICAL = "biography.type.Author";
	const FICTIONAL_CHARACTER_CANONICAL = "biography.type.FictionalCharacter";

	const FOLDER = "biography";
	const PATH_FILE = "photo/".self::FOLDER."/";

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
    protected $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;
	
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $dayBirth;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $monthBirth;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $yearBirth;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $dayDeath;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $monthDeath;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $yearDeath;

	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     */
    protected $country;
	
	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Language")
     */
	protected $language;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $type;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Source", mappedBy="authors")
     */
    private $sources;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Source", mappedBy="fictionalCharacters")
     */
    private $artworks;
	
	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\FileManagement")
     */
    protected $fileManagement;
	
	public function getTypeCanonical()
	{
		switch($this->type)
		{
			case self::AUTHOR:
				return self::AUTHOR_CANONICAL;
			case self::FICTIONAL_CHARACTER:
				return self::FICTIONAL_CHARACTER_CANONICAL;
		}
		
		return null;
	}
	
	public function isFictionalCharacter()
	{
		return $this->type == self::FICTIONAL_CHARACTER;
	}
	
	public function isAuthor()
	{
		return $this->type == self::AUTHOR;
	}

	public function __toString()
	{
		return $this->title;
	}

	public function __construct()
	{
		$this->type = self::AUTHOR;
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
		$this->setSlug();
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug()
    {
		if(empty($this->slug))
			$this->slug = GenericFunction::slugify($this->title);
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getDayBirth()
    {
        return $this->dayBirth;
    }

    public function setDayBirth($dayBirth)
    {
        $this->dayBirth = $dayBirth;
    }

    public function getMonthBirth()
    {
        return $this->monthBirth;
    }

    public function setMonthBirth($monthBirth)
    {
        $this->monthBirth = $monthBirth;
    }

    public function getYearBirth()
    {
        return $this->yearBirth;
    }

    public function setYearBirth($yearBirth)
    {
        $this->yearBirth = $yearBirth;
    }

    public function getDayDeath()
    {
        return $this->dayDeath;
    }

    public function setDayDeath($dayDeath)
    {
        $this->dayDeath = $dayDeath;
    }

    public function getMonthDeath()
    {
        return $this->monthDeath;
    }

    public function setMonthDeath($monthDeath)
    {
        $this->monthDeath = $monthDeath;
    }

    public function getYearDeath()
    {
        return $this->yearDeath;
    }

    public function setYearDeath($yearDeath)
    {
        $this->yearDeath = $yearDeath;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }
	
	public function getLanguage()
	{
		return $this->language;
	}
	
	public function setLanguage($language)
	{
		$this->language = $language;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function setType($type)
	{
		$this->type = $type;
	}
	
	public function getFileManagement()
	{
		return $this->fileManagement;
	}
	
	public function setFileManagement($fileManagement)
	{
		$this->fileManagement = $fileManagement;
	}
}