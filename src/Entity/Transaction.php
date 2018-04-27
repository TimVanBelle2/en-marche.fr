<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Transaction
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(length=100, nullable=true)
     */
    private $payboxResultCode;

    /**
     * @var string
     *
     * @ORM\Column(length=100, nullable=true)
     */
    private $payboxAuthorizationCode;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $payboxPayload;
    /**
     * @var Donation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Donation", inversedBy="transactions")
     */
    private $donation;

    public function getPayboxPayloadAsJson(): string
    {
        return json_encode($this->payboxPayload, JSON_PRETTY_PRINT);
    }

    public function getPayboxResultCode(): string
    {
        return $this->payboxResultCode;
    }

    public function setPayboxResultCode(string $payboxResultCode): void
    {
        $this->payboxResultCode = $payboxResultCode;
    }

    public function getPayboxAuthorizationCode(): string
    {
        return $this->payboxAuthorizationCode;
    }

    public function setPayboxAuthorizationCode(string $payboxAuthorizationCode): void
    {
        $this->payboxAuthorizationCode = $payboxAuthorizationCode;
    }

    public function getPayboxPayload(): array
    {
        return $this->payboxPayload;
    }

    public function setPayboxPayload(array $payboxPayload): void
    {
        $this->payboxPayload = $payboxPayload;
    }

    public function getDonation(): Donation
    {
        return $this->donation;
    }

    public function setDonation(Donation $donation): void
    {
        $this->donation = $donation;
    }
}
