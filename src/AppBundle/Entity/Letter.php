<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a letter in our catalog
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
     * @ORM\Column(name="originMonth", type="integer", nullable=true)
     */
    private $originMonth;

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
     * @var string
     *
     * @ORM\Column(name="recipientCategory", type="string", length=255, nullable=true)
     */
    private $recipientCategory;

    /**
     * @var string
     *
     * @ORM\Column(name="home", type="string", length=255, nullable=true)
     */
    private $home;

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
     * @ORM\OneToMany(targetEntity="Document", mappedBy="letter")
     */
    private $documents;

    /**
     * Letter constructor.
     */
    public function __construct()
    {
        $this->setDateCreated(new \DateTime());
        $this->documents = new ArrayCollection();
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
            'originMonth'       => $this->originMonth,
            'rating'            => $this->rating,
            'letterType'        => $this->letterType,
            'recipientCategory' => $this->recipientCategory,
            'home'              => $this->home,
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $nameFirst
     * @return $this
     */
    public function setNameFirst($nameFirst)
    {
        $this->nameFirst = $nameFirst;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameFirst()
    {
        return $this->nameFirst;
    }

    /**
     * @param string $nameLast
     * @return $this
     */
    public function setNameLast($nameLast)
    {
        $this->nameLast = $nameLast;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameLast()
    {
        return $this->nameLast;
    }

    /**
     * @param integer $originYear
     * @return $this
     */
    public function setOriginYear($originYear)
    {
        $this->originYear = $originYear;

        return $this;
    }

    /**
     * @return int
     */
    public function getOriginYear()
    {
        return $this->originYear;
    }

    /**
     * @return int
     */
    public function getOriginMonth()
    {
        return $this->originMonth;
    }

    /**
     * @param int $originMonth
     * @return $this
     */
    public function setOriginMonth($originMonth)
    {
        $this->originMonth = $originMonth;

        return $this;
    }

    /**
     * @param integer $rating
     * @return $this
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param integer $letterType
     * @return $this
     */
    public function setLetterType($letterType)
    {
        $this->letterType = $letterType;

        return $this;
    }

    /**
     * @return string
     */
    public function getLetterType()
    {
        return $this->letterType;
    }

    /**
     * @param string $recipientCategory
     * @return $this
     */
    public function setRecipientCategory($recipientCategory)
    {
        $this->recipientCategory = $recipientCategory;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientCategory()
    {
        return $this->recipientCategory;
    }

    /**
     * @return string
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * @param string $home
     * @return $this
     */
    public function setHome($home)
    {
        $this->home = $home;

        return $this;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param \DateTime $dateCreated
     * @return $this
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param Document $document
     * @return $this
     */
    public function addDocument(Document $document)
    {
        $this->documents[] = $document;

        return $this;
    }

    /**
     * @param Document $document
     */
    public function removeDocument(Document $document)
    {
        $this->documents->removeElement($document);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocuments()
    {
        return $this->documents;
    }
}
