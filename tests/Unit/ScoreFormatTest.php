<?php

namespace Tests\Unit;

use App\Support\Score;
use PHPUnit\Framework\TestCase;

class ScoreFormatTest extends TestCase
{
    public function test_only_a_perfect_score_drops_the_decimal(): void
    {
        $this->assertSame('5', Score::format(5.0));
        $this->assertSame('5', Score::format(4.97)); // rounds to 5,0 → 5
    }

    public function test_every_other_score_keeps_one_decimal_with_a_comma(): void
    {
        $this->assertSame('4,0', Score::format(4.0));
        $this->assertSame('1,0', Score::format(1.0));
        $this->assertSame('4,5', Score::format(4.5));
        $this->assertSame('4,3', Score::format(4.33));
        $this->assertSame('2,8', Score::format(2.75));
    }
}
