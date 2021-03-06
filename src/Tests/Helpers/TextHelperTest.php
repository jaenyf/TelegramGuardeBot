<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TelegramGuardeBot\Helpers\TextHelper;

final class TextHelperTest extends TestCase
{
    public function testNormalizeWithUpperCaseReturnNormalizedString(): void
    {
        //Act
        $tested = TextHelper::normalize("๐๐๐ชDฤ๐ฑแณ๐จ๐๐ฑ๐๐โณ๐๏ผฏแญโตแ๐ข๐ฑ๐๐๐๐๐จโจ");

        //Assert
        $this->assertThat($tested, $this->equalTo('ABCDEFGHIJKLMNOPQRSTUVWXYZ'));
    }

    public function testNormalizeWithLowerCaseReturnNormalizedString(): void
    {
        //Act
        $tested = TextHelper::normalize("รฅ๐ซ๐๏ฝรซแบวง๐แป๐ฟโฑชlโฟ๊แดฯฑ๐ศ๐ฐลง๐ฆ๐ฟิ๐กลทลผ");

        //Assert
        $this->assertThat($tested, $this->equalTo('abcdefghijklmnopqrstuvwxyz'));
    }

    public function testNormalizeRespectCasing(): void
    {
        //Act
        $tested = TextHelper::normalize("๐ษ๐ข๐ญฤ๐ฏแณาป๏ผฉ๐ณ๐l๐๐ท๏ผฏ๐แญล๐ฦซโ๐ถแแฝ๐จ๐");

        //Assert
        $this->assertThat($tested, $this->equalTo('AbCdEfGhIjKlMnOpQrStUvWxYz'));
    }

    public function testNormalizeDoesNotAlterInputText(): void
    {
        //Arrange
        $input = "๐ษ๐ข๐ญฤ๐ฏแณาป๏ผฉ๐ณ๐l๐๐ท0๐แญล๐ฦซโ๐ถแแฝ๐จ๐";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->logicalNot($this->equalTo($input)));
    }

    public function testNormalizeRemoveDiacritic() : void
    {
        //Arrange
        $input = "รรรรรรรรรลธ ร รงรฉรจรชรซรดรถรปรผรฟ";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->equalTo("AEEEEOOUUY aceeeeoouuy"));
    }

    public function testNormalizeLeftTrim() : void
    {
        //Arrange
        $input = "    This text should be left-trimmed !";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->equalTo("This text should be left-trimmed !"));
    }

    public function testNormalizeRightTrim() : void
    {
        //Arrange
        $input = "This text should be right-trimmed !    ";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->equalTo("This text should be right-trimmed !"));
    }

    public function testNormalizeTransformMultipleWhitespacesToOne() : void
    {
        //Arrange
        $input = "This text        has to many    whitespaces  !";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->equalTo("This text has to many whitespaces !"));
    }

    public function testNormalizeTransformMultilineToSingleLine() : void
    {
        //Arrange
        $input = "This\r\n is\n a \r multiline \n text !";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->equalTo("This is a multiline text !"));
    }

    public function testNormalizeRemovesControlCharacters() : void
    {
        //Arrange
        $input = "This \0 text ".chr(7)." contains \e ".chr(8)." \t control \n \v \f \r characters !";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->equalTo("This text contains control characters !"));
    }

    public function testNormalizeDoesNotReplaceDigits() : void
    {
        //Arrange
        $input = "0123456789";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->equalTo("0123456789"));
    }

    public function testNormalizeRemoveEmojis() : void
    {
        //Arrange
        $input = "This โค๏ธ text ๐ contains ๐ emojis ๐ !";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->equalTo("This text contains emojis !"));
    }

    public function testNormalizeReplaceSingleEmojiBySpace() : void
    {
        //Arrange
        $input = "Singleโค๏ธSpace";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->equalTo("Single Space"));
    }
}
