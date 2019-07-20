<?php

namespace App\Twig;

use App\Service\Captcha;
use App\Service\Gravatar;

use Doctrine\ORM\EntityManagerInterface;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class QuotusExtension extends AbstractExtension
{
	private $em;
	
	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

    public function getFilters() {
        return array(
			new TwigFilter('html_entity_decode', array($this, 'htmlEntityDecodeFilter')),
			new TwigFilter('toString', array($this, 'toStringFilter')),
			new TwigFilter('text_month', array($this, 'textMonthFilter')),
			new TwigFilter('max_size_image', array($this, 'maxSizeImageFilter'), array('is_safe' => array('html'))),
			new TwigFilter('date_letter', array($this, 'dateLetterFilter'), array('is_safe' => array('html'))),
			new TwigFilter('remove_control_characters', array($this, 'removeControlCharactersFilter')),
			new TwigFilter('base64_decode', array($this, 'base64DecodeFilter'))
        );
    }
	
	public function getFunctions() {
		return array(
			new TwigFunction('captcha', array($this, 'generateCaptcha')),
			new TwigFunction('gravatar', array($this, 'generateGravatar')),
			new TwigFunction('minify_file', array($this, 'minifyFile')),
			new TwigFunction('count_unread_messages', array($this, 'countUnreadMessagesFunction')),
			new TwigFunction('code_by_language', array($this, 'getCodeByLanguage')),
			new TwigFunction('random_image', array($this, 'randomImage'))
		);
	}

    public function getStringObject($arraySubEntity, $element) {
		if(!is_null($arraySubEntity) and array_key_exists ($element, $arraySubEntity))
			return $arraySubEntity[$element];

        return "";
    }

    public function htmlEntityDecodeFilter($str) {
        return html_entity_decode($str);
    }
	
	public function textMonthFilter($monthInt)
	{
		$arrayMonth = array("janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre");
		
		return $arrayMonth[intval($monthInt) - 1];
	}
	
	public function maxSizeImageFilter($img, array $options = [], $isPDF = false)
	{
		$basePath = ($isPDF) ? '' : '/';
		
		if(!file_exists($img))
			return '<img src="'.$basePath.'photo/640px-Starry_Night_Over_the_Rhone.jpg" alt="" style="max-width: 400px" />';
		
		$imageSize = getimagesize($img);

		$width = $imageSize[0];
		$height = $imageSize[1];
		
		$max_width = 500;
				
		if($width > $max_width)
		{
			$height = ($max_width * $height) / $width;
			$width = $max_width;
		}

		return '<img src="'.$basePath.$img.'" alt="" style="max-width: '.$width.'px;" />';
	}
	
	public function dateLetterFilter($date)
	{
		$arrayMonth = array("janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre");
		
		$month = $arrayMonth[$date->format("n") - 1];
		
		$day = ($date->format("j") == 1) ? $date->format("j")."<sup>er</sup>" : $date->format("j");
		
		return $day." ".$month." ".$date->format("Y");
	}

	public function removeControlCharactersFilter($string)
	{
		return preg_replace("/[^a-zA-Z0-9 .\-_;!:?äÄöÖüÜß<>='\"]/", "", $string);
	}
	
	public function base64DecodeFilter($string)
	{
		return base64_decode($string);
	}
	
	public function generateCaptcha($request)
	{
		$captcha = new Captcha($request->getSession());

		$wordOrNumberRand = rand(1, 2);
		$length = rand(3, 7);

		if($wordOrNumberRand == 1)
			$word = $captcha->wordRandom($length);
		else
			$word = $captcha->numberRandom($length);
		
		return $captcha->generate($word);
	}

	public function generateGravatar()
	{
		$gr = new Gravatar();

		return $gr->getURLGravatar();
	}

	public function countUnreadMessagesFunction()
	{
		return $this->em->getRepository("App\Entity\Contact")->countUnreadMessages();
	}
	
	public function minifyFile($file)
	{
		$mn = new \App\Service\MinifyFile($file);
		return $mn->save();
	}
	
	public function randomImage($entity) {
		$imageArray = [];
		
		foreach($entity->getQuoteImages() as $image)
			$imageArray[] = $image->getImage();

		if(empty($imageArray))
			return null;
		
		return $imageArray[array_rand($imageArray)];
	}
	
	public function getCodeByLanguage($locale)
	{
		switch($locale)
		{
			case "en":
				return "en_GB";
			default:
				return "fr_FR";
		}
	}
}