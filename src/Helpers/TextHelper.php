<?php

namespace TelegramGuardeBot\Helpers;

/**
 * @author jaenyf
 */
class TextHelper
{
    private const homoglyphes = array(
        "'" => "’",
        "\"" => "«»",
        "a" => "𝒶ã⍺α𝜶𝛼ǎɑâ𝖆𝖺𝑎а𝐚𝛂𝗮ａáạä𝓪àăåȧa𝒂𝞪𝕒𝔞𝚊𝝰ą𝙖𝘢",
        "b" => "𝗯𝖇ЬḇƅᏏᖯḅ𝓫𝕓𝑏ḃɓ𝘣𝙗Ƅ𝐛ｂ𝒃𝒷𝖻𝔟b𝚋ʙ",
        "c" => "ç𐐽ᴄⲥ𝖼𝘤𝒸𝙘𝒄𝓬ꮯϲ𝐜с𝕔𝗰𝔠𺀠c𝚌𝑐𝖈ⅽｃ",
        "d" => "𝗱ꓒ𝙙𝕕ԁᏧ𝒹ɗ𝖽𝘥ḏďｄ𝒅dɖ𝚍ᑯⅾ𝓭𝐝ḓ𝑑ժḑḋ𝔡đcḍb𝖉ⅆ",
        "e" => "ꬲ𝖊𝕖𝚎℮êė𝔢ⅇȩҽ𝖾ē𝒆ḛĕ𝑒ɇ𝓮ẹℯ𝙚ę𝘦ée𝐞ëèеěcｅ𝗲",
        "f" => "𝔣𝙛ꞙ𝒻𝚏ƒｆ𝑓𝗳ẝf𝕗𝒇𝟋𝓯ք𝘧ꬵſ𝖿𝐟𝖋ϝḟꜰ",
        "g" => "ɡᶃ𝗴𝔤ɢǧ𝐠g𝘨ģ𝕘ｇնցġℊ𝗀ĝ𝒈ǥ𝚐ƍ𝓰𝙜𝑔𝖌ğǵ",
        "h" => "ħȟհᏂⱨ𝚑ẖһ𝔥𝒽ḥḩ𝖍ℎ𝕙𝘩𝗁𝐡ɦ𝒉𝗵ｈhĥḧ𝓱𝙝ḣḫ",
        "i" => "îι𝝸iᛁɨꭵا𝚤𰰠𝐢𝑖𝕚𝚒ȋᎥ˛𐐠⍳𝜄𝗂ιіꙇⅰ𝛊ɪ𝖎ỉīĭｉͺ𝒾𝓲íɩℹ𝔦𝗶𑣃𝜾𝞲ịǐïⅈı𝘪ì𝒊",
        "j" => "𝔧𝚓jϳ𝗷𝐣𝙟𝒋𝗃𝒿𝑗𝖏ɉ𝘫ʝјⅉ𝕛ｊ𝓳",
        "k" => "𝐤𝑘𝗄𝗸𝚔ḳḵ𝓴𝓀ｋκⱪ𝕜k𝔨𝖐𝒌ķ𝙠𝘬ᴋ",
        "l" => "l",
        "m" => "ᴍｍmṁⅿḿṃɱ",
        "n" => "𝓃ｎ𝚗ñ𝐧𝒏ռ𝙣mꞑ𝗇𝘯ṅńņ𝓷𝗻ǹɴnṇň𝑛ṉո𝕟𝖓𝔫",
        "o" => "ôöᴏοоｏ",
        "p" => "ƥ𝗉ṗ𝝆ṕ𝒑𝛒𝕡𝔭ｐ𝚙ρ𝝔⍴𝜌𝞀𝓹ƿϱⲣ𝑝𝖕𝞺p𝓅𝐩р𝙥𝞎𝘱𝛠𝗽𝟈𝜚ᴘꮲᴩ",
        "q" => "𝐪𝖖𝑞գｑʠq𝘲𝚚ԛ𝕢𝓆𝔮𝗾𝒒𝗊Ⴓ𝙦զ𝓺",
        "r" => "𝓇𝐫ṛrᴦꭈ𝑟ɼṙṟ𝘳ꭇ𝗿ȑ𝗋Իгɾŕɍȓ𝔯ⲅŗｒ𝒓ř𝙧ʀɽ𝚛ꮁ𝖗𝕣𝓻",
        "s" => "𵠠ꜱ𑣁𝘀ႽЅṣƽ𝓼ŝṡ𐑈Ｓʂ𝑠𐐠ś𝙨𝓈𝖘sｓ𝕤Ꮪ𝐬𝔰𝗌𝚜ѕ𝒔ꮪ𝘴șšՏ",
        "t" => "𝐭𝒕𝑡ṫᎢ𝖙ț𝘁𝓽ƫτ𝔱ţ𝙩ｔ𝓉𝗍𝘵𝚝ṭt𝕥ŧ",
        "u" => "𝕦𑣘ůūǔùUꭎｕՍＵųűư𝗎ꞟʉսûԱ𝖚𝐮𝞄𝘂𝘶𝛖𐓶𝜐úuũȗụ𝒖𝓊𝔲üυμ𝝊ʋ𝑢ŭȕ𝞾𝓾𝙪𝚞ᴜꭒ",
        "v" => "𝒗⋁𝚟𑣀𝗏ѵѴ𝜈𝞶𝑣𝓋𝐯𝔳𝘃ｖ𝝼vⱱνטⱴ𝖛ᴠ𝘷∨ⅴ𝙫𝕧ṽꮩ𝓿ṿᶌ𑜆𝛎𝝂",
        "w" => "𑜊ẅ𝑤𑜎𝘄ẘ𝖜ɯ𝒘𝔀𝗐𝘸vｗ𝚠ẇẁ𝐰ẉWwẃ𝔴ԝꮃ𝕨աⱳ𑜏𝙬Ꮃŵᴡ𝓌ѡ",
        "x" => "ｘ𝐱⤬𝘅𝙭𝓍𝔁ᕽᕁ𝗑𝖝𝑥᙮х𝚡𝔵×⤫ⅹχ𝒙𝘹x⨯𝕩",
        "y" => "𝛄𝕪𝓎ʏɣ𝗒𝒚Үŷγƴ𝚢ỿ𝛾𑣜ℽ𝞬ɏꭚẏ𝔂𝔶ყ𝝲ỵ𝘆ү𝖞ȳyýÿу𝘺𝙮𝑦ᶌ𝐲𝜸",
        "z" => "𝖟𝕫𝙯ꮓź𝘻ｚᏃ𝗓𝔃𝘇ʐƶż𝐳ⱬẕ𝓏𝒛ᴢẓ𝑧𝚣𑣄𝔷z",
        "A" => "𝗔𝖠𝙰𝘈A𝘼𝜜ꭺᗅ𝒜ꓮ𝔸Ꭺ𝓐𝚨ÅÁ𝔄𝝖𝐴À𝞐𐊠ᴀÂ𝐀ÃА𖽀𝑨𝕬𝛢ＡÄΑ",
        "B" => "𝛣𝞑ᗷß𝗕ꞴＢ𝖡𝜝𐊡Β𝑩𝔹𝓑𝔅в𝘉ᛒ𝐵𝙱𝝗ꓐВ𝐁ᏼ𝚩ℬBβ𐌁𐊂𝘽ʙ𝕭Ᏼ",
        "C" => "Ⲥ𝑪𑣩🝌Ｃℭ𝙲𝒞ꓚ𝓒Ꮯ𐊢ℂ𝐶C𐔜𝗖𝐂Ⅽ𐌂𑣲С𝘾𝘊𺀠𝖢𝕮Ϲ𐐕𐐠",
        "D" => "𝓓𝗗ᗞ𝔻𝘿Đᗪ𝐷𝙳𝖣𝒟ĎꓓDⅅⅮ𝕯𝔇ᴅ𝐃𝑫𝘋ＤᎠꭰ",
        "E" => "𝙀ÈĚ𝔈Éᴇ𝘌𝔼Е𑢦𝜠Ēℰ⋿𝝚ĔΕË𝛦𝑬𝚬𝗘𝞔ꭼĖE𝕰ＥĘ𑢮𝖤𝙴𐊆𝓔𝐸ꓰÊ𝐄Ꭼⴹ",
        "F" => "𝙁𝔽𝑭F𝙵ꓝ𝐹ᖴ𝟊𐊇𝐅𝈓Ꞙ𝕱𝔉𝓕𝖥ℱ𝗙𑢢Ｆ𑣂𐔥𐊥Ϝ𝘍",
        "G" => "𝘎Ｇԍ𝗚ɢ𝐺𝔾𝙂𝑮𝕲Ꮐնꮐ𝒢ᏻꓖԌ𝓖G𝔊𝐆𝙶𝖦Ᏻ",
        "H" => "𝞖𝐇𝝜𝗛ℍ𝛨𝘏Ⲏ𝖧𝜢𝙷ꓧнᎻℋꮋ𝚮Hᕼ𝓗𝐻𝑯ʜ𝙃Η𐋏Ｈ𝕳Нℌ",
        "I" => "ι𝝸Ⅰᛁꭵاӏ𝚤Ι𰰠𝐢𝑖І𝕚𝚒Ꭵ˛𐐠⍳𝜄I𝗂ιіꙇⅰ𝛊ɪ𝖎ī𝙞ｉͺ𝒾𝓲ɩℹ𝔦𝗶𑣃𝜾𝞲ⅈı𝘪Ｉ𝒊",
        "J" => "𝔍𝓙Ꭻ𝕁ᴊＪ𝒥𝙹𝑱Ϳ𝙅յ𝐽Jꭻ𝗝𝕵Јᒍ𝐉ꓙ𝖩Ʝ𝘑",
        "K" => "𝙆𝛫𝑲𝐾𝕂𝒦𝞙𝓚𝖪𝘒К𝝟ᛕꓗ𝙺𐔘KK𝚱𝔎𝐊𝗞Ⲕ𝕶ᏦΚ𝜥Ｋ",
        "L" => "ι𐑃ＬⳐ𝕃𝙇𑢣L𝐋𝓛𝔏𝕷𝈪l𐐛𝙻𝐿ⳑʟⅬ𝑳𐔦ꓡ𝘓Ꮮᒪℒ𝖫ꮮ𖼖ⅼ𝗟𑢲",
        "M" => "𝜧Ꮇℳ𝛭𝝡Μ𝞛𝑀𝗠𝙼𝔐Ϻ𝚳𐊰𝐌𝙈Ⅿ𝘔ᗰМ𝖬𐌑𝓜MꓟᛖⲘ𝑴𝕸Ｍ𝕄",
        "N" => "𐔓𝑵𝝢𝙽Ｎ𝚴𝒩𝞜𝙉𝕹ℕ𝐍Ⲛ𝛮𝘕𝔑𝖭𝜨Nɴ𝗡ꓠ𝑁𝓝Ν",
        "O" => "ÔÖΟՕО𱠠ＯO𐐠",
        "P" => "𝙋𝑷𝜬Ꮲ𝞠𝚸ℙ𝘗𝙿РᑭΡꓑ𝐏𝝦𝓟𐊕𝖯𝛲ⲢPＰ𝔓𝑃𝒫𝗣𝕻",
        "Q" => "Qℚ𝖰𝙌𝚀𝗤𝘘𝐐ႳႭ𝑄ⵕ𝕼𝑸𝒬𝔔𝓠Ｑ",
        "R" => "ꭱ𐒴𝘙Ｒ𝑅ℝꮢᖇℛᚱℜ𝚁𝐑ƦR𝈖ꓣ𝕽𝙍𖼵Ꭱ𝖱𝗥𝑹Ꮢʀ𝓡",
        "S" => "Ꮥ𵠠𝚂𝗦𝖲ႽЅ𝐒𝑺𝕊Ｓ𝕾𝑆𖼺𐊖S𝙎𝒮𝓢ꓢsｓ𝘚ᏚՏѕ𝔖𐐠",
        "T" => "⟙𝞣𑢼Ꭲ𐊗𝞽Τтᴛ𝐓𝒯𝙏𝖳𝜏Ⲧτ𝕋ꭲ𝑻𝗧𝑇Ｔ𐊱𐌕𖼊T𝛕𝚻𝝉⊤Т𝔗ꓔ𝜯𝝩𝘛𝛵🝨𝞃𝕿𝓣𝚃",
        "U" => "ÛÜ𖽂𝔘𝓤𐓎𝚄ՍUＵ𝗨𝑼Ա𝙐⋃u𑢸𝑈μ𝖀υ𝐔ሀ∪𝕌𝖴ꓴᑌ𝒰𝘜",
        "V" => "𝙑𝑽ꓦᏙ𝚅ѴⅤ𝐕𝔙𖼈Ｖ𝕍ꛟ𝖁𝗩𝘝𝈍V۷٧𑢠𐔝ⴸ𝖵𝑉𝓥𝒱ᐯ",
        "W" => "𝘞𝗪𝖂𝐖𑣦Ԝ𝖶Ｗ𝓦𝕎ｗꓪW𝙒wᏔ𝚆𝑊𝔚𑣯𝒲𝑾Ꮃ",
        "X" => "ｘ𝕏𐌗𐊴Ꭓ𝒳𝛸Ｘ𝖃𝜲𝔛ꓫ𑣬𝘟𝓧Ⅹ𝐗𝞦Χ𝚇𐔧𝑋╳𐊐𝚾𝗫ᚷ𝙓XⲬⵝ𝝬𝑿χ𐌢Х𝖷᙭",
        "Y" => "ŸᎩ𝝪𝒀ʏｙ𝖄Ү𝐘ϒ𝒴γ𖽃𝙔𝚼𝔜𑢤Ꮍ𝚈𝞤ꓬy𝓨у𝘠𝛶ＹY𝑌УⲨ𝗬𐊲𝜰Υ𝖸𝕐",
        "Z" => "𝚉𝐙𝒵𝙕ℨℤ𝘡𝞕𝒁𝖹Ꮓ𝛧𝓩𑢩Ζ𝜡𐋵𝖅ꓜＺ𝝛𝗭𝑍𑣥𝚭Z"
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

    /**
     * Trim, set text to a single line and remove multiples contiguous whitespaces
     */
    public static function toSingleNormalizedLine($text)
    {
        $text = preg_replace('/\s\s+/', ' ', $text);
        $text = trim($text);
        return $text;
    }
}
