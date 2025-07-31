<?php

namespace App\Entity;

use App\Repository\RendementCalculatorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RendementCalculatorRepository::class)]
class RendementCalculator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "Le montant doit être positif")]
    #[Assert\NotNull(message: "Le montant investi est obligatoire")]
    private ?float $montantInvesti = null;



    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: 0,
        max: 1,
        notInRangeMessage: "Le taux doit être entre 0 et 100%"
    )]
    #[Assert\NotNull(message: "Le taux de rendement est obligatoire")]
    private ?float $tauxRendement = null;



    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "La durée doit être positive")]
    #[Assert\LessThanOrEqual(50, message: "Maximum 50 ans")]
    #[Assert\NotNull(message: "La durée est obligatoire")]
    private ?int $dureeAnnees = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateCalcul = null;

    public function __construct()
    {
        $this->dateCalcul = new \DateTime();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontantInvesti(): ?float
    {
        return $this->montantInvesti;
    }

    public function setMontantInvesti(?float $montantInvesti): static
    {
        $this->montantInvesti = $montantInvesti;

        return $this;
    }

    public function getTauxRendement(): ?float
    {
        return $this->tauxRendement;
    }

    public function setTauxRendement(?float $tauxRendement): static
    {
        $this->tauxRendement = $tauxRendement;

        return $this;
    }

    public function getDureeAnnees(): ?int
    {
        return $this->dureeAnnees;
    }

    public function setDureeAnnees(?int $dureeAnnees): static
    {
        $this->dureeAnnees = $dureeAnnees;

        return $this;
    }

    public function getDateCalcul(): ?\DateTime
    {
        return $this->dateCalcul;
    }

    public function setDateCalcul(?\DateTime $dateCalcul): static
    {
        $this->dateCalcul = $dateCalcul;

        return $this;
    }


    public function calculerRendementAnnuel(): float
    {
        if ($this->montantInvesti === null || $this->tauxRendement === null) {
            return 0.0;
        }

        return $this->montantInvesti * $this->tauxRendement;
    }

    /**
     * Calcule le capital total avec intérêts composés
     */
    public function calculerRendementTotal(): float
    {
        if ($this->montantInvesti === null || $this->tauxRendement === null || $this->dureeAnnees === null) {
            return 0.0;
        }

        // Formule : Capital × (1 + taux)^années
        return $this->montantInvesti * pow((1 + $this->tauxRendement), $this->dureeAnnees);
    }

    /**
     * Calcule les revenus mensuels
     */
    public function calculerRevenusMensuels(): float
    {
        return $this->calculerRendementAnnuel() / 12;
    }

    /**
     * Génère la projection année par année
     */
    public function getProjectionAnnuelle(): array
    {
        if ($this->montantInvesti === null || $this->tauxRendement === null || $this->dureeAnnees === null) {
            return [];
        }

        $projection = [];
        $capital = $this->montantInvesti;

        for ($annee = 1; $annee <= $this->dureeAnnees; $annee++) {
            $revenus = $capital * $this->tauxRendement;
            $capital += $revenus;

            $projection[] = [
                'annee' => $annee,
                'capital' => round($capital, 2),
                'revenus' => round($revenus, 2),
                'capitalAccumule' => round($capital - $this->montantInvesti, 2)
            ];
        }

        return $projection;
    }

    /**
     * Méthode utilitaire pour résumé rapide
     */
    public function getResume(): array
    {
        return [
            'montantInvesti' => $this->montantInvesti,
            'tauxRendement' => $this->tauxRendement * 100, // Conversion en %
            'dureeAnnees' => $this->dureeAnnees,
            'rendementAnnuel' => $this->calculerRendementAnnuel(),
            'rendementTotal' => $this->calculerRendementTotal(),
            'revenusMensuels' => $this->calculerRevenusMensuels(),
            'dateCalcul' => $this->dateCalcul?->format('Y-m-d H:i:s')
        ];
    }
}
