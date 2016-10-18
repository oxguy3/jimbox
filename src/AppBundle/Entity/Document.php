<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a file (pdf/jpg/etc) attached to a Letter
 *
 * @ORM\Table(name="document")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DocumentRepository")
 */
class Document
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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Letter", inversedBy="documents")
     * @ORM\JoinColumn(name="letter_id", referencedColumnName="id")
     */
    private $letter;

    /**
     * This is not a column in the database but it's mapping a field from the form
     *
     * @Assert\File(maxSize="100000000", mimeTypes={ "application/pdf", "image/jpeg", "image/png" })
     */
    public $file;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    

    /**
     * @param Letter $letter
     * @return $this
     */
    public function setLetter(Letter $letter = null)
    {
        $this->letter = $letter;

        return $this;
    }

    /**
     * @return Letter
     */
    public function getLetter()
    {
        return $this->letter;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        $this->name = $file->getClientOriginalName();

        return $this;
    }
}
