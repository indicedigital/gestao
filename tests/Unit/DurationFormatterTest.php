<?php

namespace Tests\Unit;

use App\Support\DurationFormatter;
use PHPUnit\Framework\TestCase;

class DurationFormatterTest extends TestCase
{
    public function test_formats_sub_hour_values_as_minutes(): void
    {
        $this->assertSame('15 min', DurationFormatter::format(0.25));
        $this->assertSame('30 min', DurationFormatter::format(0.5));
        $this->assertSame('45 min', DurationFormatter::format(0.75));
    }

    public function test_formats_hour_and_mixed_values(): void
    {
        $this->assertSame('1h', DurationFormatter::format(1.0));
        $this->assertSame('1h 30 min', DurationFormatter::format(1.5));
        $this->assertSame('2h 15 min', DurationFormatter::format(2.25));
    }

    public function test_converts_minutes_input_to_decimal_hours(): void
    {
        $this->assertSame(0.25, DurationFormatter::toHours(15, 'minutes'));
        $this->assertSame(0.5, DurationFormatter::toHours(30, 'minutes'));
        $this->assertSame(2.0, DurationFormatter::toHours(120, 'minutes'));
    }

    public function test_keeps_existing_hour_values_compatible(): void
    {
        $this->assertSame(0.25, DurationFormatter::toHours(0.25, 'hours'));
        $this->assertSame(2.5, DurationFormatter::toHours(2.5, 'hours'));
    }
}
