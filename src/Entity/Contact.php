<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContactRepository")
 */
class Contact
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
    protected $subject;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $mail;

    /**
     * @ORM\Column(type="text")
     */
    protected $message;

    /**
     * @ORM\Column(type="string", length=1)
     */
    protected $readMessage;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $dateSending;

	public function __construct()
	{
		$this->readMessage = 0;
		$this->dateSending = new \DateTime();
	}

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getReadMessage()
    {
        return $this->readMessage;
    }

    public function setReadMessage($readMessage)
    {
        $this->readMessage = $readMessage;
    }

    public function getDateSending()
    {
        return $this->dateSending;
    }

    public function setDateSending($dateSending)
    {
        $this->dateSending = $dateSending;
    }
}