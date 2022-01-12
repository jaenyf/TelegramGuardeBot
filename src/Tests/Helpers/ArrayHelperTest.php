<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use TelegramGuardeBot\Helpers\ArrayHelper;

final class ArrayHelperTest extends TestCase
{
    public function testToObjectConvertAnArrayToAnObject(): void
    {
        //Act
        $tested = ArrayHelper::toObject([]);

        //Assert
        $this->assertFalse(is_array($tested));
        $this->assertInstanceOf(\stdClass::class, $tested);
    }
}