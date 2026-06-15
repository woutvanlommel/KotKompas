<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientCreditsException extends RuntimeException
{
    public function __construct(public int $required, public int $balance)
    {
        parent::__construct("Onvoldoende credits: {$required} nodig, saldo is {$balance}.");
    }
}
