<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TelegramGuardeBot\Helpers\TextHelper;

final class TextHelperTest extends TestCase
{
    public function testNormalizeWithUpperCaseReturnNormalizedString(): void
    {
        //Act
        $tested = TextHelper::normalize("𝒜𝓑𝑪DĖ𝕱Ᏻ𝛨𝙞𝑱𝝟𝓛ℳ𝓝Ｏᑭⵕᖇ𝓢𐊱𝖀𝑉𝕎𝖃𝓨ℨ");

        //Assert
        $this->assertThat($tested, $this->equalTo('ABCDEFGHIJKLMNOPQRSTUVWXYZ'));
    }

    public function testNormalizeWithLowerCaseReturnNormalizedString(): void
    {
        //Act
        $tested = TextHelper::normalize("å𝓫𝕔ｄëẝǧ𝕙ỉ𝒿ⱪlⅿꞑᴏϱ𝓆ȑ𝔰ŧ𝕦𝓿ԝ𝚡ŷż");

        //Assert
        $this->assertThat($tested, $this->equalTo('abcdefghijklmnopqrstuvwxyz'));
    }

    public function testNormalizeRespectCasing(): void
    {
        //Act
        $tested = TextHelper::normalize("𝓐ɓ𐊢𝓭Ĕ𝓯ᏳһＩ𝓳𝞙l𝔐𝓷Ｏ𝜌Ⴍŕ𝑆ƫ⋃𝞶Ꮤᕽ𝓨𝔃");

        //Assert
        $this->assertThat($tested, $this->equalTo('AbCdEfGhIjKlMnOpQrStUvWxYz'));
    }

    public function testNormalizeDoesNotAlterInputText(): void
    {
        //Arrange
        $input = "𝓐ɓ𐊢𝓭Ĕ𝓯ᏳһＩ𝓳𝞙l𝔐𝓷0𝜌Ⴍŕ𝑆ƫ⋃𝞶Ꮤᕽ𝓨𝔃";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->logicalNot($this->equalTo($input)));
    }

    public function testNormalizeRemoveDiacritic() : void
    {
        //Arrange
        $input = "ÀÉÈÊËÔÖÛÜŸ àçéèêëôöûüÿ";

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
        $input = "This ❤️ text 😀 contains 🙏 emojis 👍 !";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->equalTo("This text contains emojis !"));
    }

    public function testNormalizeReplaceSingleEmojiBySpace() : void
    {
        //Arrange
        $input = "Single❤️Space";

        //Act
        $tested = TextHelper::normalize($input);

        //Assert
        $this->assertThat($tested, $this->equalTo("Single Space"));
    }
}
