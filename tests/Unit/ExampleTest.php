<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * It asserts that true is true.
     */
    #[Test]
    public function it_asserts_true_is_true()
    {
        // Arrange
        // No setup needed

        // Act
        $result = true;

        // Assert
        $this->assertTrue($result);
    }
}
