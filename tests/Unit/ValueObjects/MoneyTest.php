<?php

namespace Tests\Unit\ValueObjects;

use App\Domain\ValueObjects\Money;
use InvalidArgumentException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MoneyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_created_with_valid_amount_and_currency(): void
    {
        $money = new Money(100.50, 'EUR');

        $this->assertEquals(100.50, $money->getAmount());
        $this->assertEquals('EUR', $money->getCurrency());
    }

    /** @test */
    public function it_can_be_created_with_integer_amount(): void
    {
        $money = new Money(100, 'DKK');

        $this->assertEquals(100, $money->getAmount());
        $this->assertEquals('DKK', $money->getCurrency());
    }

    /** @test */
    public function it_throws_exception_for_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative');

        new Money(-50, 'EUR');
    }

    /** @test */
    public function it_throws_exception_for_non_numeric_amount(): void
    {
        // PHP type declarations will throw TypeError before our validation runs
        $this->expectException(\TypeError::class);

        new Money('invalid', 'EUR');
    }

    /** @test */
    public function it_throws_exception_for_invalid_currency(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency must be a 3-letter code');

        new Money(100, 'EU');
    }

    /** @test */
    public function it_adds_two_money_objects_with_same_currency(): void
    {
        $money1 = new Money(100, 'EUR');
        $money2 = new Money(50.50, 'EUR');

        $result = $money1->add($money2);

        $this->assertEquals(150.50, $result->getAmount());
        $this->assertEquals('EUR', $result->getCurrency());
    }

    /** @test */
    public function it_throws_exception_when_adding_different_currencies(): void
    {
        $money1 = new Money(100, 'EUR');
        $money2 = new Money(50, 'USD');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot add money with different currencies');

        $money1->add($money2);
    }

    /** @test */
    public function it_multiplies_money_by_integer(): void
    {
        $money = new Money(25.50, 'EUR');

        $result = $money->multiply(4);

        $this->assertEquals(102.00, $result->getAmount());
        $this->assertEquals('EUR', $result->getCurrency());
    }

    /** @test */
    public function it_multiplies_money_by_zero(): void
    {
        $money = new Money(100, 'EUR');

        $result = $money->multiply(0);

        $this->assertEquals(0, $result->getAmount());
        $this->assertEquals('EUR', $result->getCurrency());
    }

    /** @test */
    public function it_formats_money_correctly(): void
    {
        $money = new Money(1234.56, 'EUR');

        $this->assertEquals('1.234,56 EUR', $money->format());
    }

    /** @test */
    public function it_formats_money_with_zero_amount(): void
    {
        $money = new Money(0, 'EUR');

        $this->assertEquals('0,00 EUR', $money->format());
    }

    /** @test */
    public function it_checks_if_amount_is_zero(): void
    {
        $zeroMoney = new Money(0, 'EUR');
        $nonZeroMoney = new Money(10, 'EUR');

        $this->assertTrue($zeroMoney->isZero());
        $this->assertFalse($nonZeroMoney->isZero());
    }

    /** @test */
    public function it_creates_zero_money(): void
    {
        $zeroMoney = Money::zero('DKK');

        $this->assertEquals(0, $zeroMoney->getAmount());
        $this->assertEquals('DKK', $zeroMoney->getCurrency());
        $this->assertTrue($zeroMoney->isZero());
    }

    /** @test */
    public function it_implements_to_string(): void
    {
        $money = new Money(99.99, 'USD');

        $this->assertEquals('99,99 USD', (string) $money);
    }

    /** @test */
    public function it_is_immutable(): void
    {
        $original = new Money(100, 'EUR');
        $added = $original->add(new Money(50, 'EUR'));

        // Original should be unchanged
        $this->assertEquals(100, $original->getAmount());
        $this->assertEquals(150, $added->getAmount());
    }

    /** @test */
    public function it_compares_by_value(): void
    {
        $money1 = new Money(100, 'EUR');
        $money2 = new Money(100, 'EUR');
        $money3 = new Money(100, 'USD');

        // Same value, different instances
        $this->assertEquals($money1->getAmount(), $money2->getAmount());
        $this->assertEquals($money1->getCurrency(), $money2->getCurrency());

        // Different currency
        $this->assertNotEquals($money1->getCurrency(), $money3->getCurrency());
    }
}
