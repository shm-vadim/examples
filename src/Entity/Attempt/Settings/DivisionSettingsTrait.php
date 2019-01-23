<?php

namespace App\Entity\Attempt\Settings;

trait DivisionSettingsTrait
{
    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumDividend;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumDividend;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumDivisor;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumDivisor;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumQuotient;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumQuotient;

    public function getMinimumDividend(): float
    {
        return $this->minimumDividend;
    }

    public function setMinimumDividend(float $minimumDividend): void
    {
        $this->minimumDividend = $minimumDividend;
    }

    public function getMaximumDividend(): float
    {
        return $this->maximumDividend;
    }

    public function setMaximumDividend(float $maximumDividend): void
    {
        $this->maximumDividend = $maximumDividend;
    }

    public function getMinimumDivisor(): float
    {
        return $this->minimumDivisor;
    }

    public function setMinimumDivisor(float $minimumDivisor): void
    {
        $this->minimumDivisor = $minimumDivisor;
    }

    public function getMaximumDivisor(): float
    {
        return $this->maximumDivisor;
    }

    public function setMaximumDivisor(float $maximumDivisor): void
    {
        $this->maximumDivisor = $maximumDivisor;
    }

    public function getMinimumQuotient(): float
    {
        return $this->minimumQuotient;
    }

    public function setMinimumQuotient(float $minimumQuotient): void
    {
        $this->minimumQuotient = $minimumQuotient;
    }

    public function getMaximumQuotient(): float
    {
        return $this->maximumQuotient;
    }

    public function setMaximumQuotient(float $maximumQuotient): void
    {
        $this->maximumQuotient = $maximumQuotient;
    }
}