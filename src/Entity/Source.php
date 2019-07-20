<?php

namespace App\Entity;

use App\Service\GenericFunction;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SourceRepository")
 */
class Source
{
	const BOOK = "book";
	const MOVIE = "movie";
	const TV_SERIES = "tvseries";
	
	const BOOK_CANONICAL = "source.type.Book";
	const MOVIE_CANONICAL = "source.type.Movie";
	const TV_SERIES_CANONICAL = "source.type.Tvseries";

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
     * @ORM\Column(type="string", length=255)
     */
    protected $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;
	
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $photo;
	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Language")
     */
	protected $language;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Biography", inversedBy="sources", cascade={"persist"})
	 * @ORM\JoinTable(name="source_author")
     */
	protected $authors;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Biography", inversedBy="artworks", cascade={"persist"})
	 * @ORM\JoinTable(name="artwork_fictionalcharacter")
     */
	protected $fictionalCharacters;
	
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

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    public function getDayBirth()
    {
        return $this->dayBirth;
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

    /**
     * Add authors
     *
     * @param App\Entity\Biography $authors
     */
	public function addAuthor(\App\Entity\Biography $biography)
	{
		$this->authors[] = $biography;
	}

    /**
     * Set authors
     *
     * @param string $authors
     */
    public function setAuthors($authors)
    {
        $this->authors = $authors;
    }

    /**
     * Remove authors
     *
     * @param App\Entity\Biography $authors
     */
	public function removeAuthor($biography)
	{
		$this->authors->removeElement(biography);
	}

    /**
     * Get authors
     *
     * @return Doctrine\Common\Collections\Collection
     */
	public function getAuthors()
	{
		return $this->authors;
	}

    /**
     * Add fictionalCharacters
     *
     * @param App\Entity\Biography $fictionalCharacters
     */
	public function addFictionalCharacter($fictionalCharacter)
	{
		$this->fictionalCharacters[] = $fictionalCharacter;
	}

    /**
     * Set fictionalCharacters
     *
     * @param string $fictionalCharacters
     */
    public function setFictionalCharacters($fictionalCharacters)
    {
        $this->fictionalCharacters = $fictionalCharacters;
    }

    /**
     * Remove fictionalCharacters
     *
     * @param App\Entity\Biography $fictionalCharacters
     */
	public function removeFictionalCharacter($fictionalCharacter)
	{
		$this->fictionalCharacters->removeElement($fictionalCharacter);
	}

    /**
     * Get fictionalCharacters
     *
     * @return Doctrine\Common\Collections\Collection
     */
	public function getFictionalCharacters()
	{
		return $this->fictionalCharacters;
	}
}