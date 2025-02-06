<?php

namespace App\ValueObjects;

use Stringable;

class Phone implements Stringable
{
    /**
     * @var bool
     */
    protected $isSanitized = false;

    /**
     * @var bool
     */
    protected $isValid = true;

    /**
     * @var string
     */
    protected $native;

    /**
     * @var string
     */
    protected $sanitized = '';

    public function __construct(string $native = '')
    {
        $this->native = $native;
        $this->sanitize();
    }

    public function __toString()
    {
        return $this->getSanitized();
    }

    public function getSanitized(): string
    {
        return $this->sanitized;
    }

    public function isSanitized(): bool
    {
        return $this->isSanitized;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    protected function sanitize()
    {
        $phone = preg_replace('/[^0-9]/', '', $this->native);

        $firstNumber = $phone[0] ?? '';
        if ((strlen($phone) === 10) && ($firstNumber === '9')) {
            $phone = "7" . $phone;
        }

        if (strlen($phone) !== 11) {
            $this->isValid = false;

            return;
        }

        $firstNumber = $phone[0] ?? '';
        if (!in_array($firstNumber, ['8', '7'])) {
            $this->isValid = false;

            return;
        }

        if ($firstNumber === '8') {
            $phone[0] = '7';
        }

        $this->sanitized = $phone;

        if ($this->sanitized === $this->native) {
            $this->isSanitized = true;
        }
    }
}
