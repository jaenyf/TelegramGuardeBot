<?php

namespace TelegramGuardeBot\Helpers;

/**
 * @author jaenyf
 */
class TextHelper
{
    private const homoglyphes = array(
        "'" => "โ",
        "\"" => "ยซยป",
        "a" => "๐ถรฃโบฮฑ๐ถ๐ผวษรข๐๐บ๐ะฐ๐๐๐ฎ๏ฝรกแบกรค๐ชร ฤรฅศงa๐๐ช๐๐๐๐ฐฤ๐๐ข",
        "b" => "๐ฏ๐ะฌแธฦแแฏแธ๐ซ๐๐แธษ๐ฃ๐ฦ๐๏ฝ๐๐ท๐ป๐b๐ส",
        "c" => "รง๐ฝแดโฒฅ๐ผ๐ค๐ธ๐๐๐ฌ๊ฎฏฯฒ๐ั๐๐ฐ๐ ๐บ c๐๐๐โฝ๏ฝ",
        "d" => "๐ฑ๊๐๐ิแง๐นษ๐ฝ๐ฅแธฤ๏ฝ๐dษ๐แฏโพ๐ญ๐แธ๐ีชแธแธ๐กฤcแธb๐โ",
        "e" => "๊ฌฒ๐๐๐โฎรชฤ๐ขโศฉาฝ๐พฤ๐แธฤ๐ษ๐ฎแบนโฏ๐ฤ๐ฆรฉe๐รซรจะตฤc๏ฝ๐ฒ",
        "f" => "๐ฃ๐๊๐ป๐ฦ๏ฝ๐๐ณแบf๐๐๐๐ฏึ๐ง๊ฌตลฟ๐ฟ๐๐ฯแธ๊ฐ",
        "g" => "ษกแถ๐ด๐คษขวง๐ g๐จฤฃ๐๏ฝีถึฤกโ๐ฤ๐วฅ๐ฦ๐ฐ๐๐๐ฤวต",
        "h" => "ฤงศีฐแโฑจ๐แบาป๐ฅ๐ฝแธฅแธฉ๐โ๐๐ฉ๐๐กษฆ๐๐ต๏ฝhฤฅแธง๐ฑ๐แธฃแธซ",
        "i" => "รฎฮน๐ธiแษจ๊ญตุง๐ค๐ฐฐ ๐ข๐๐๐ศแฅห๐ โณ๐๐แพพั๊โฐ๐ษช๐แปฤซฤญ๏ฝอบ๐พ๐ฒรญษฉโน๐ฆ๐ถ๐ฃ๐พ๐ฒแปวรฏโฤฑ๐ชรฌ๐",
        "j" => "๐ง๐jฯณ๐ท๐ฃ๐๐๐๐ฟ๐๐ษ๐ซสัโ๐๏ฝ๐ณ",
        "k" => "๐ค๐๐๐ธ๐แธณแธต๐ด๐๏ฝฮบโฑช๐k๐จ๐๐ฤท๐ ๐ฌแด",
        "l" => "l",
        "m" => "แด๏ฝmแนโฟแธฟแนษฑ",
        "n" => "๐๏ฝ๐รฑ๐ง๐ีผ๐ฃm๊๐๐ฏแนลล๐ท๐ปวนษดnแนล๐แนีธ๐๐๐ซ",
        "o" => "รดรถแดฮฟะพ๏ฝ",
        "p" => "ฦฅ๐แน๐แน๐๐๐ก๐ญ๏ฝ๐ฯ๐โด๐๐๐นฦฟฯฑโฒฃ๐๐๐บp๐๐ฉั๐ฅ๐๐ฑ๐ ๐ฝ๐๐แด๊ฎฒแดฉ",
        "q" => "๐ช๐๐ีฃ๏ฝส q๐ฒ๐ิ๐ข๐๐ฎ๐พ๐๐แณ๐ฆีฆ๐บ",
        "r" => "๐๐ซแนrแดฆ๊ญ๐ษผแนแน๐ณ๊ญ๐ฟศ๐ิปะณษพลษศ๐ฏโฒล๏ฝ๐ล๐งสษฝ๐๊ฎ๐๐ฃ๐ป",
        "s" => "๐ต  ๊ฑ๐ฃ๐แฝะแนฃฦฝ๐ผลแนก๐๏ผณส๐ ๐ ล๐จ๐๐s๏ฝ๐คแ๐ฌ๐ฐ๐๐ั๐๊ฎช๐ดศลกี",
        "t" => "๐ญ๐๐กแนซแข๐ศ๐๐ฝฦซฯ๐ฑลฃ๐ฉ๏ฝ๐๐๐ต๐แนญt๐ฅลง",
        "u" => "๐ฆ๐ฃลฏลซวรนU๊ญ๏ฝี๏ผตลณลฑฦฐ๐๊สีฝรปิฑ๐๐ฎ๐๐๐ถ๐๐ถ๐รบuลฉศแปฅ๐๐๐ฒรผฯฮผ๐ส๐ขลญศ๐พ๐พ๐ช๐แด๊ญ",
        "v" => "๐โ๐๐ฃ๐ัตัด๐๐ถ๐ฃ๐๐ฏ๐ณ๐๏ฝ๐ผvโฑฑฮฝืโฑด๐แด ๐ทโจโด๐ซ๐งแนฝ๊ฎฉ๐ฟแนฟแถ๐๐๐",
        "w" => "๐แบ๐ค๐๐แบ๐ษฏ๐๐๐๐ธv๏ฝ๐ แบแบ๐ฐแบWwแบ๐ดิ๊ฎ๐จีกโฑณ๐๐ฌแณลตแดก๐ัก",
        "x" => "๏ฝ๐ฑโคฌ๐๐ญ๐๐แฝแ๐๐๐ฅแฎั๐ก๐ตรโคซโนฯ๐๐นxโจฏ๐ฉ",
        "y" => "๐๐ช๐สษฃ๐๐าฎลทฮณฦด๐ขแปฟ๐พ๐ฃโฝ๐ฌษ๊ญแบ๐๐ถแง๐ฒแปต๐าฏ๐ศณyรฝรฟั๐บ๐ฎ๐ฆแถ๐ฒ๐ธ",
        "z" => "๐๐ซ๐ฏ๊ฎลบ๐ป๏ฝแ๐๐๐สฦถลผ๐ณโฑฌแบ๐๐แดขแบ๐ง๐ฃ๐ฃ๐ทz",
        "A" => "๐๐ ๐ฐ๐A๐ผ๐๊ญบแ๐๊ฎ๐ธแช๐๐จรร๐๐๐ดร๐๐ แดร๐ระ๐ฝ๐จ๐ฌ๐ข๏ผกรฮ",
        "B" => "๐ฃ๐แทร๐๊ด๏ผข๐ก๐๐กฮ๐ฉ๐น๐๐ะฒ๐แ๐ต๐ฑ๐๊ะ๐แผ๐ฉโฌBฮฒ๐๐๐ฝส๐ญแด",
        "C" => "โฒค๐ช๐ฃฉ๐๏ผฃโญ๐ฒ๐๊๐แ๐ขโ๐ถC๐๐๐โญ๐๐ฃฒะก๐พ๐๐บ ๐ข๐ฎฯน๐๐ ",
        "D" => "๐๐แ๐ป๐ฟฤแช๐ท๐ณ๐ฃ๐ฤ๊Dโโฎ๐ฏ๐แด๐๐ซ๐๏ผคแ ๊ญฐ",
        "E" => "๐รฤ๐รแด๐๐ผะ๐ขฆ๐ ฤโฐโฟ๐ฤฮร๐ฆ๐ฌ๐ฌ๐๐๊ญผฤE๐ฐ๏ผฅฤ๐ขฎ๐ค๐ด๐๐๐ธ๊ฐร๐แฌโดน",
        "F" => "๐๐ฝ๐ญF๐ต๊๐นแด๐๐๐๐๊๐ฑ๐๐๐ฅโฑ๐๐ขข๏ผฆ๐ฃ๐ฅ๐ฅฯ๐",
        "G" => "๐๏ผงิ๐ษข๐บ๐พ๐๐ฎ๐ฒแีถ๊ฎ๐ขแป๊ิ๐G๐๐๐ถ๐ฆแณ",
        "H" => "๐๐๐๐โ๐จ๐โฒ๐ง๐ข๐ท๊งะฝแปโ๊ฎ๐ฎHแผ๐๐ป๐ฏส๐ฮ๐๏ผจ๐ณะโ",
        "I" => "ฮน๐ธโ แ๊ญตุงำ๐คฮ๐ฐฐ ๐ข๐ะ๐๐แฅห๐ โณ๐I๐แพพั๊โฐ๐ษช๐ฤซ๐๏ฝอบ๐พ๐ฒษฉโน๐ฆ๐ถ๐ฃ๐พ๐ฒโฤฑ๐ช๏ผฉ๐",
        "J" => "๐๐แซ๐แด๏ผช๐ฅ๐น๐ฑอฟ๐ีต๐ฝJ๊ญป๐๐ตะแ๐๊๐ฉ๊ฒ๐",
        "K" => "๐๐ซ๐ฒ๐พ๐๐ฆ๐๐๐ช๐ะ๐แ๊๐บ๐Kโช๐ฑ๐๐๐โฒ๐ถแฆฮ๐ฅ๏ผซ",
        "L" => "ฮน๐๏ผฌโณ๐๐๐ขฃL๐๐๐๐ท๐ชl๐๐ป๐ฟโณสโฌ๐ณ๐ฆ๊ก๐แแชโ๐ซ๊ฎฎ๐ผโผ๐๐ขฒ",
        "M" => "๐งแทโณ๐ญ๐กฮ๐๐๐ ๐ผ๐ฯบ๐ณ๐ฐ๐๐โฏ๐แฐะ๐ฌ๐๐M๊แโฒ๐ด๐ธ๏ผญ๐",
        "N" => "๐๐ต๐ข๐ฝ๏ผฎ๐ด๐ฉ๐๐๐นโ๐โฒ๐ฎ๐๐๐ญ๐จNษด๐ก๊ ๐๐ฮ",
        "O" => "รรฮีะ๐ฑ  ๏ผฏO๐ ",
        "P" => "๐๐ท๐ฌแข๐ ๐ธโ๐๐ฟะ แญฮก๊๐๐ฆ๐๐๐ฏ๐ฒโฒขP๏ผฐ๐๐๐ซ๐ฃ๐ป",
        "Q" => "Qโ๐ฐ๐๐๐ค๐๐แณแญ๐โต๐ผ๐ธ๐ฌ๐๐ ๏ผฑ",
        "R" => "๊ญฑ๐ด๐๏ผฒ๐โ๊ฎขแโแฑโ๐๐ฦฆR๐๊ฃ๐ฝ๐๐ผตแก๐ฑ๐ฅ๐นแส๐ก",
        "S" => "แ๐ต  ๐๐ฆ๐ฒแฝะ๐๐บ๐๏ผณ๐พ๐๐ผบ๐S๐๐ฎ๐ข๊ขs๏ฝ๐แีั๐๐ ",
        "T" => "โ๐ฃ๐ขผแข๐๐ฝฮคัแด๐๐ฏ๐๐ณ๐โฒฆฯ๐๊ญฒ๐ป๐ง๐๏ผด๐ฑ๐๐ผT๐๐ป๐โคะข๐๊๐ฏ๐ฉ๐๐ต๐จ๐๐ฟ๐ฃ๐",
        "U" => "รร๐ฝ๐๐ค๐๐ีU๏ผต๐จ๐ผิฑ๐โu๐ขธ๐ฮผ๐ฯ๐แโช๐๐ด๊ดแ๐ฐ๐",
        "V" => "๐๐ฝ๊ฆแ๐ัดโค๐๐๐ผ๏ผถ๐๊๐๐ฉ๐๐V?ทูง๐ข ๐โดธ๐ต๐๐ฅ๐ฑแฏ",
        "W" => "๐๐ช๐๐๐ฃฆิ๐ถ๏ผท๐ฆ๐๏ฝ๊ชW๐wแ๐๐๐๐ฃฏ๐ฒ๐พแณ",
        "X" => "๏ฝ๐๐๐ด๊ณ๐ณ๐ธ๏ผธ๐๐ฒ๐๊ซ๐ฃฌ๐๐งโฉ๐๐ฆฮง๐๐ง๐โณ๐๐พ๐ซแท๐Xโฒฌโต๐ฌ๐ฟฯ๐ขะฅ๐ทแญ",
        "Y" => "ลธแฉ๐ช๐ส๏ฝ๐าฎ๐ฯ๐ดฮณ๐ฝ๐๐ผ๐๐ขคแฝ๐๐ค๊ฌy๐จั๐ ๐ถ๏ผนY๐ะฃโฒจ๐ฌ๐ฒ๐ฐฮฅ๐ธ๐",
        "Z" => "๐๐๐ต๐โจโค๐ก๐๐๐นแ๐ง๐ฉ๐ขฉฮ๐ก๐ต๐๊๏ผบ๐๐ญ๐๐ฃฅ๐ญZ"
    );

    private static function removeEmojis( $string ) {
        $string = str_replace( "{%}", "\\{\\%\\}", $string );
        $string = str_replace( "?", "{%}", $string );
        $string  = mb_convert_encoding( $string, "ISO-8859-1", "UTF-8" );
        $string  = mb_convert_encoding( $string, "UTF-8", "ISO-8859-1" );
        $string  = str_replace( array( "?", "? ", " ?" ), array(" "), $string );
        $string  = str_replace( "{%}", "?", $string );
        $string = str_replace( "\\{\\%\\}", "{%}", $string );
        return $string;
    }

    /**
     * Translate homoglyphes to their normalized one and clean text
     */
    public static function normalize($text, $processHomoglyphes = true)
    {
        $text = trim($text);

        if($processHomoglyphes)
        {
            $text = mb_str_split($text);

            foreach ($text as &$letter) {
                foreach (TextHelper::homoglyphes as $replacement => $glyphes) {
                    if (mb_strstr($glyphes, $letter)) {
                        $letter = $replacement;
                        break;
                    }
                }
            }
            $text = (implode('', $text));
        }

        $text = preg_replace('/[[:cntrl:]]/', ' ', $text);

        $text = self::removeEmojis($text);
        $text = preg_replace('/\s+/', ' ', $text);

        return $text;
    }
}
