<?php

namespace App\Entity;

use App\Repository\RobertKochInstituteRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RobertKochInstituteRepository::class)
 */
class RobertKochInstitute
{
    const FIELD_ENTITY_CREATE_DATE_TIME = 'entityCreateDateTime';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $entityCreateDateTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $lastPageUpdateDateTime;

    /**
     * @ORM\Column(type="string")
     * @var string $pageContentHash
     */
    private string $pageContentHash;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->setEntityCreateDateTime(new DateTime());
    }

    public function getEntityCreateDateTime(): ?\DateTimeInterface
    {
        return $this->entityCreateDateTime;
    }

    public function setEntityCreateDateTime(\DateTimeInterface $entityCreateDateTime): self
    {
        $this->entityCreateDateTime = $entityCreateDateTime;

        return $this;
    }

    public function getLastPageUpdateDateTime(): ?\DateTimeInterface
    {
        return $this->lastPageUpdateDateTime;
    }

    public function setLastPageUpdateDateTime(?\DateTimeInterface $lastPageUpdateDateTime): self
    {
        $this->lastPageUpdateDateTime = $lastPageUpdateDateTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getPageContentHash(): string
    {
        return $this->pageContentHash;
    }

    /**
     * @param string $pageContentHash
     */
    public function setPageContentHash(string $pageContentHash): void
    {
        $this->pageContentHash = $pageContentHash;
    }

}
