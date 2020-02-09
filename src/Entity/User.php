<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
	const FOLDER = "user";
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
    protected $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $roles;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $avatar;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $gravatar;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $presentation;
	
	/**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     */
    protected $country;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $salt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $token;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $expired_at;

    public function __construct($username = "", $password = "", $salt = "", array $roles = array("ROLE_USER"), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true)
    {
        $this->username = $username;
        $this->password = $password;
		$this->salt = $salt;
        $this->enabled = $enabled;
        $this->accountNonExpired = $userNonExpired;
        $this->credentialsNonExpired = $credentialsNonExpired;
        $this->roles = implode(",", $roles);
    }

	public function __toString()
	{
		return $this->username;
	}
	
    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->salt
        ) = unserialize($serialized);
    }
	
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getRoles()
    {
        return explode(",", $this->roles);
    }

    public function setRoles($roles)
    {
        $this->roles = implode(",", $roles);
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }
	
	public function getGravatar()
    {
        return $this->gravatar;
    }

    public function setGravatar($gravatar)
    {
        $this->gravatar = $gravatar;
    }

    public function getPresentation()
    {
        return $this->presentation;
    }

    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }
	
	public function setSalt($salt)
	{
		$this->salt = $salt;
	}

	public function getToken()
	{
		return $this->token;
	}
	
	public function setToken($token)
	{
		$this->token = $token;
	}

	public function getExpiredAt()
	{
		return $this->expired_at;
	}
	
	public function setExpiredAt($expired_at)
	{
		$this->expired_at = $expired_at;
	}

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }
	
    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return $this->accountNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return $this->credentialsNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}