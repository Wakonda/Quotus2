<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use App\Service\GenericFunction;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuoteRepository")
 */
class Quote
{
	const BIOGRAPHY_AUTHORTYPE = "biography";
	const USER_AUTHORTYPE = "user";

	const PUBLISHED_STATE = 0;
	const DRAFT_STATE = 1;
	const DELETE_STATE = 2;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $text;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $slug;

	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     */
    protected $country;

   /**
    * @ORM\OneToMany(targetEntity=QuoteImage::class, cascade={"persist", "remove"}, mappedBy="quote", orphanRemoval=true)
    */
    protected $quoteImages;
	
	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Language")
     */
	protected $language;

	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Biography")
     */
    protected $biography;

	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Source")
     */
    protected $source;
	
	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    protected $user;

    /**
     * @ORM\Column(type="integer")
     */
    protected $state;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $authorType;
	
	public function isBiographyAuthorType()
	{
		return $this->authorType == self::BIOGRAPHY_AUTHORTYPE;
	}
	
	public function isUserAuthorType()
	{
		return $this->authorType == self::USER_AUTHORTYPE;
	}

	public function getStateString()
	{
		$res = "";
		
		switch($this->state)
		{
			case 0:
				$res = "quote.state.Published";
				break;
			case 1:
				$res = "quote.state.Draft";
				break;
			case 2:
				$res = "quote.state.Deleted";
				break;
			default:
				$res = "";
		}
		
		return $res;
	}

	public function getStateRealName()
	{
		$res = "";
		
		switch($this->state)
		{
			case 0:
				$res = "published";
				break;
			case 1:
				$res = "draft";
				break;
			case 2:
				$res = "deleted";
				break;
			default:
				$res = "";
		}
		
		return $res;
	}

    public function __construct()
    {
        $this->quoteImages = new ArrayCollection();
		$this->authorType = self::BIOGRAPHY_AUTHORTYPE;
    }
	
	public function isBiography()
	{
		return $this->authorType == self::BIOGRAPHY_AUTHORTYPE;
	}

	public function isUser()
	{
		return $this->authorType == self::USER_AUTHORTYPE;
	}
	
	public function getAuthor()
	{
		if($this->isBiography())
			return $this->biography;
		else
			return $this->user;
	}

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
		$this->setSlug();
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug()
    {
		if(empty($this->slug))
			$this->slug = GenericFunction::slugify($this->text, 30);
    }

	public function getLanguage()
	{
		return $this->language;
	}
	
	public function setLanguage($language)
	{
		$this->language = $language;
	}

    public function getQuoteImages()
    {
        return $this->quoteImages;
    }
     
    public function addQuoteImage(QuoteImage $quoteImage)
    {
        $this->quoteImages->add($quoteImage);
        $quoteImage->setQuote($this);
    }
	
    public function removeQuoteImage(QuoteImage $quoteImage)
    {
        $quoteImage->setQuote(null);
        $this->quoteImages->removeElement($quoteImage);
    }

    public function getBiography()
    {
        return $this->biography;
    }

    public function setBiography($biography)
    {
        $this->biography = $biography;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function getAuthorType()
    {
        return $this->authorType;
    }

    public function setAuthorType($authorType)
    {
        $this->authorType = $authorType;
    }
}