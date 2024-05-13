<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Tests\Unit\Helper;

use App\Entity\UserSettings;
use App\Helper\TokenHelper;
use PHPUnit\Framework\TestCase;

class TokenHelperTest extends TestCase
{
    private TokenHelper $tokenHelper;

    private UserSettings $userSettings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenHelper = new TokenHelper();
        $this->userSettings = $this->createMock(UserSettings::class);
    }

    public function testIsValidNullTokenExpiry(): void
    {
        $this->userSettings->expects(self::once())
            ->method('getGoogleDriveTokenExpiry')
            ->willReturn(null);

        $this->assertFalse($this->tokenHelper->isValid($this->userSettings));
    }

    public function testIsValidNullTokenIssueTime(): void
    {
        $this->userSettings->expects(self::once())
            ->method('getGoogleDriveTokenExpiry')
            ->willReturn(3600);

        $this->userSettings->expects(self::once())
            ->method('getGoogleDriveTokenIssueTime')
            ->willReturn(null);

        $this->assertFalse($this->tokenHelper->isValid($this->userSettings));
    }

    public function testIsValidTokenExpired(): void
    {
        $this->userSettings->expects(self::once())
            ->method('getGoogleDriveTokenExpiry')
            ->willReturn(3600);

        $this->userSettings->expects(self::once())
            ->method('getGoogleDriveTokenIssueTime')
            ->willReturn(1712544805);

        $this->assertFalse($this->tokenHelper->isValid($this->userSettings));
    }

    public function testIsValid(): void
    {
        $this->userSettings->expects(self::once())
            ->method('getGoogleDriveTokenExpiry')
            ->willReturn(3600);

        $this->userSettings->expects(self::once())
            ->method('getGoogleDriveTokenIssueTime')
            ->willReturn(time());

        $this->assertTrue($this->tokenHelper->isValid($this->userSettings));
    }
}
