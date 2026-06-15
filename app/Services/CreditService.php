<?php

namespace App\Services;

use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function balance(User $user): int
    {
        return (int) $user->creditTransactions()->sum('amount');
    }

    /**
     * Credits bijschrijven. Idempotent op stripe_session_id.
     * Geeft null terug als de sessie al verwerkt was.
     */
    public function add(User $user, int $amount, string $reason, ?string $stripeSessionId = null): ?CreditTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Bedrag moet positief zijn.');
        }

        if (
            $stripeSessionId !== null
            && CreditTransaction::where('stripe_session_id', $stripeSessionId)->exists()
        ) {
            return null; // al verwerkt
        }

        try {
            return $user->creditTransactions()->create([
                'amount' => $amount,
                'reason' => $reason,
                'stripe_session_id' => $stripeSessionId,
            ]);
        } catch (QueryException $e) {
            // unique-violation = gelijktijdige dubbele webhook; veilig negeren
            if ($stripeSessionId !== null) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Credits verbruiken. Gooit InsufficientCreditsException bij te weinig saldo.
     */
    public function spend(User $user, int $amount, string $reason): CreditTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Bedrag moet positief zijn.');
        }

        return DB::transaction(function () use ($user, $amount, $reason) {
            $balance = (int) $user->creditTransactions()->lockForUpdate()->sum('amount');

            if ($balance < $amount) {
                throw new InsufficientCreditsException($amount, $balance);
            }

            return $user->creditTransactions()->create([
                'amount' => -$amount,
                'reason' => $reason,
            ]);
        });
    }
}
