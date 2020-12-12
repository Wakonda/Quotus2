<?php

namespace App\Entity;

use App\Service\GenericFunction;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StoreRepository")
 */
class Store
{
	const FOLDER = "store";
	const PATH_FILE = "photo/".self::FOLDER."/";

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $text;
	
	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Biography")
     */
    protected $biography;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $amazonCode;

	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Language")
     */
	protected $language;

    /**
     * @ORM\Column(type="text")
     */
    protected $embedCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $photo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $slug;

	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Source")
     */
    protected $source;

	private $partnerId = "activiparano-21";
	
	public function getExternalStoreLink()
	{
		return "http://www.amazon.fr/dp/".$this->amazonCode."/ref=nosim?tag=".$this->partnerId;
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

	public function getLanguage()
	{
		return $this->language;
	}
	
	public function setLanguage($language)
	{
		$this->language = $language;
	}

    /**
     * Set text
     *
     * @param text $text
     */
    public function setText($text)
    {
		$this->text = $text;
    }

    /**
     * Get text
     *
     * @return text 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set biography
     *
     * @param string $biography
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;
    }

    /**
     * Get biography
     *
     * @return string 
     */
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * Set amazonCode
     *
     * @param integer $amazonCode
     */
    public function setAmazonCode($amazonCode)
    {
        $this->amazonCode = $amazonCode;
    }

    /**
     * Get amazonCode
     *
     * @return integer 
     */
    public function getAmazonCode()
    {
        return $this->amazonCode;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    public function getEmbedCode()
    {
        return $this->embedCode;
    }

    public function setEmbedCode($embedCode)
    {
        $this->embedCode = $embedCode;
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

    public function getSource()
    {
        return $this->source;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }
}