<?php

namespace Tests\Unit\Services;

use App\Services\Paginator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaginatorTest extends TestCase
{
    #[Test]
    public function it_paginates_the_filtered_results(): void
    {
        $result = (new Paginator)->paginate(range(1, 17), 2, 5);

        $this->assertSame([6, 7, 8, 9, 10], $result['items']);
        $this->assertSame([
            'current_page' => 2,
            'per_page' => 5,
            'total' => 17,
            'last_page' => 4,
        ], $result['meta']);
    }

    #[Test]
    public function it_clamps_the_requested_page_to_the_last_available_page(): void
    {
        $result = (new Paginator)->paginate(range(1, 17), 999, 5);

        $this->assertSame([16, 17], $result['items']);
        $this->assertSame(4, $result['meta']['current_page']);
        $this->assertSame(4, $result['meta']['last_page']);
    }

    #[Test]
    public function it_returns_an_empty_result_when_there_are_no_items(): void
    {
        $result = (new Paginator)->paginate([], 1, 20);

        $this->assertSame([], $result['items']);
        $this->assertSame([
            'current_page' => 1,
            'per_page' => 20,
            'total' => 0,
            'last_page' => 1,
        ], $result['meta']);
    }
}
