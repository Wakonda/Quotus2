<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PageRepository")
 */
class Page
{
	const FOLDER = "page";
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
     * @ORM\Column(type="text")
     */
    protected $text;
	
    /**
     * @ORM\Column(type="string", length=255)
     */
	protected $internationalName;
	
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $photo;
	
	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Language")
     */
	protected $language;

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

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    public function getInternationalName()
    {
        return $this->internationalName;
    }

    public function setInternationalName($internationalName)
    {
        $this->internationalName = $internationalName;
    }

	public function getLanguage()
	{
		return $this->language;
	}
	
	public function setLanguage($language)
	{
		$this->language = $language;
	}
}