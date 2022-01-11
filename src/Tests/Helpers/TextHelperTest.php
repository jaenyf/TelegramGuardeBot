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
        $tested = TextHelper::normalize("𝓐ɓ𐊢𝓭Ĕ𝓯ᏳһＩ𝓳𝞙l𝔐𝓷0𝜌Ⴍŕ𝑆ƫ⋃𝞶Ꮤᕽ𝓨𝔃");

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
}
