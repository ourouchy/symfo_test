<?php

namespace App\Entity;

use App\Repository\SCPIRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SCPIRepository::class)]
class SCPI
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $rendement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getRendement(): ?float
    {
        return $this->rendement;
    }

    public function setRendement(float $rendement): static
    {
        $this->rendement = $rendement;

        return $this;
    }
}
