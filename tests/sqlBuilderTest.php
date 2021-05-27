<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * RumSqlBuilder Unit Test
 */
class sqlBuilderTest extends TestCase
{
    public function testBase()
    {
        self::assertEquals('相同', '相同');
    }
}
