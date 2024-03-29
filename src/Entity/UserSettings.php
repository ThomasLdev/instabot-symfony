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

    #[ORM\Column(length: 255)]
    private ?string $googleDriveToken = null;

    #[ORM\Column(length: 255)]
    private ?string $googleDriveFolderId = null;

    #[ORM\Column(length: 255)]
    private ?string $instagramToken = null;

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

    public function getGoogleDriveFolderId(): ?string
    {
        return $this->googleDriveFolderId;
    }

    public function setGoogleDriveFolderId(string $googleDriveFolderId): static
    {
        $this->googleDriveFolderId = $googleDriveFolderId;

        return $this;
    }

    public function getInstagramToken(): ?string
    {
        return $this->instagramToken;
    }

    public function setInstagramToken(string $instagramToken): static
    {
        $this->instagramToken = $instagramToken;

        return $this;
    }
}
