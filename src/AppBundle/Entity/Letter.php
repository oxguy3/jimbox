<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Letter
 *
 * @ORM\Table(name="letter")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LetterRepository")
 */
class Letter implements \JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nameFirst", type="string", length=255, nullable=true)
     */
    private $nameFirst;

    /**
     * @var string
     *
     * @ORM\Column(name="nameLast", type="string", length=255, nullable=true)
     */
    private $nameLast;

    /**
     * @var int
     *
     * @ORM\Column(name="originYear", type="integer", nullable=true)
     */
    private $originYear;

    /**
     * @var int
     *
     * @ORM\Column(name="rating", type="integer", nullable=true)
     */
    private $rating;

    /**
     * @var string
     *
     * @ORM\Column(name="letterType", type="string", length=255, nullable=true)
     */
    private $letterType;

    /**
     * @var int
     *
     * @ORM\Column(name="recipientCategory", type="integer", nullable=true)
     */
    private $recipientCategory;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=16384, nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreated", type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * Letter constructor.
     */
    public function __construct()
    {
        $this->setDateCreated(new \DateTime());
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'                => $this->id,
            'nameFirst'         => $this->nameFirst,
            'nameLast'          => $this->nameLast,
            'originYear'        => $this->originYear,
            'rating'            => $this->rating,
            'letterType'        => $this->letterType,
            'recipientCategory' => $this->recipientCategory,
            'comment'           => $this->comment,
            'dateCreated'       => $this->dateCreated
        ];
    }

    /**
     * Get first + last names combined
     *
     * @return string
     */
    public function getNameFull()
    {
        if ($this->nameFirst != null && $this->nameLast != '') {
            return $this->nameLast.', '.$this->nameFirst;
        } else {
            return $this->nameLast;
        }
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nameFirst
     *
     * @param string $nameFirst
     *
     * @return Letter
     */
    public function setNameFirst($nameFirst)
    {
        $this->nameFirst = $nameFirst;

        return $this;
    }

    /**
     * Get nameFirst
     *
     * @return string
     */
    public function getNameFirst()
    {
        return $this->nameFirst;
    }

    /**
     * Set nameLast
     *
     * @param string $nameLast
     *
     * @return Letter
     */
    public function setNameLast($nameLast)
    {
        $this->nameLast = $nameLast;

        return $this;
    }

    /**
     * Get nameLast
     *
     * @return string
     */
    public function getNameLast()
    {
        return $this->nameLast;
    }

    /**
     * Set originYear
     *
     * @param integer $originYear
     *
     * @return Letter
     */
    public function setOriginYear($originYear)
    {
        $this->originYear = $originYear;

        return $this;
    }

    /**
     * Get originYear
     *
     * @return int
     */
    public function getOriginYear()
    {
        return $this->originYear;
    }

    /**
     * Set rating
     *
     * @param integer $rating
     *
     * @return Letter
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set letterType
     *
     * @param integer $letterType
     *
     * @return Letter
     */
    public function setLetterType($letterType)
    {
        $this->letterType = $letterType;

        return $this;
    }

    /**
     * Get letterType
     *
     * @return string
     */
    public function getLetterType()
    {
        return $this->letterType;
    }

    /**
     * Set recipientCategory
     *
     * @param string $recipientCategory
     *
     * @return Letter
     */
    public function setRecipientCategory($recipientCategory)
    {
        $this->recipientCategory = $recipientCategory;

        return $this;
    }

    /**
     * Get recipientCategory
     *
     * @return int
     */
    public function getRecipientCategory()
    {
        return $this->recipientCategory;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Letter
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return Letter
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
}

