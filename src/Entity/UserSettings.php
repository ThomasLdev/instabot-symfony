<?php

namespace App\Entity;

use App\Repository\UserSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSettingsRepository::class)]
class UserSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleDriveToken = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoogleDriveToken(): ?string
    {
        return $this->googleDriveToken;
    }

    public function setGoogleDriveToken(?string $googleDriveToken): static
    {
        $this->googleDriveToken = $googleDriveToken;

        return $this;
    }
}
