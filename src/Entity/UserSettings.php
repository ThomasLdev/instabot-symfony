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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $googleDriveToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleDriveFolderId = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $instagramToken = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $googleDriveAuthCode = null;

    #[ORM\Column(nullable: true)]
    private ?int $googleDriveTokenExpiry = null;

    #[ORM\Column(nullable: true)]
    private ?int $googleDriveTokenIssueTime = null;

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

    public function setGoogleDriveFolderId(?string $googleDriveFolderId): static
    {
        $this->googleDriveFolderId = $googleDriveFolderId;

        return $this;
    }

    public function getInstagramToken(): ?string
    {
        return $this->instagramToken;
    }

    public function setInstagramToken(?string $instagramToken): static
    {
        $this->instagramToken = $instagramToken;

        return $this;
    }

    public function getGoogleDriveAuthCode(): ?string
    {
        return $this->googleDriveAuthCode;
    }

    public function setGoogleDriveAuthCode(?string $googleDriveAuthCode): static
    {
        $this->googleDriveAuthCode = $googleDriveAuthCode;

        return $this;
    }

    public function getGoogleDriveTokenExpiry(): ?int
    {
        return $this->googleDriveTokenExpiry;
    }

    public function setGoogleDriveTokenExpiry(?int $googleDriveTokenExpiry): static
    {
        $this->googleDriveTokenExpiry = $googleDriveTokenExpiry;

        return $this;
    }

    public function getGoogleDriveTokenIssueTime(): ?int
    {
        return $this->googleDriveTokenIssueTime;
    }

    public function setGoogleDriveTokenIssueTime(?int $googleDriveTokenIssueTime): static
    {
        $this->googleDriveTokenIssueTime = $googleDriveTokenIssueTime;

        return $this;
    }
}
