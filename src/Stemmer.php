<?php

namespace UkrainianStemmer;

/**
 * Algorithm provided by Dr Martin Porter
 */
class Stemmer
{
    static $INFINITIVE = '/(ти|учи|ячи|вши|ши|ати|яти|ючи|)$/u';
    static $PERFECTIVEGROUND = '/(ив|ивши|ившись((?<=[ая])(в|вши|вшись)))$/u';
    static $REFLEXIVE = '/(с[яьи])$/u';
    static $ADJECTIVE = '/(ими|ій|ий|а|е|ова|ове|ів|є|їй|єє|еє|я|ім|ем|им|ім|их|іх|ою|йми|іми|у|ю|ого|ому|ої)$/u';
    static $PARTICIPLE = '/(ий|ого|ому|им|ім|а|ій|у|ою|ій|і|их|йми|их)$/u';
    static $VERB = '/(сь|ся|ив|ать|ять|у|ю|ав|али|учи|ячи|вши|ши|е|ме|ати|яти|є)$/u';
    static $NOUN = '/(а|ев|ов|е|ями|ами|еи|и|ей|ой|ий|й|иям|ям|ием|ем|ам|ом|о|у|ах|иях|ях|ь|ию|ью|ю|ия|ья|я|і|ові|ї|ею|єю|ою|є|еві|ем|єм|ів|їв|\'ю)$/u';
    static $RVRE = '/^(.*?[аеиоуюяіїє])(.*)$/u';
    static $DERIVATIONAL = '/[^аеиоуюяіїє][аеиоуюяіїє]+[^аеиоуюяіїє]+[аеиоуюяіїє].*сть?$/u';

    public static function stemWord(string $word): string
    {
        $stem = mb_strtolower($word);

        //check if infinitive
        $m = preg_replace(self::$INFINITIVE, '', $word);
        if (strcmp($m, $word) !== 0) {
            return $word;
        }

        //init
        preg_match_all(self::$RVRE, $stem, $p);
        if (!$p) {
            return $word;
        }

        if (empty($p[2]) || empty($p[1][0])) {
            return $word;
        }
        $start = $p[1][0];
        $RV = $p[2][0];

        //STEP 1
        $m = preg_replace(self::$PERFECTIVEGROUND, '', $RV);
        if (strcmp($m, $RV) === 0) {
            $RV = preg_replace(self::$REFLEXIVE, '', $RV);
            $m = preg_replace(self::$ADJECTIVE, '', $RV);
            if (strcmp($m, $RV) === 0) {
                $RV = preg_replace(self::$PARTICIPLE, '', $RV);
            } else {
                $RV = $m;
                $m = preg_replace(self::$VERB, '', $RV);
                if (strcmp($m, $RV) === 0) {
                    $RV = preg_replace(self::$NOUN, '', $RV);
                } else {
                    $RV = $m;
                }
            }
        } else {
            $RV = $m;
        }

        //STEP 2
        $RV = preg_replace('/і$/u', '', $RV);

        //STEP 3
        if (preg_match(self::$DERIVATIONAL, $RV)) {
            $RV = preg_replace('/ість?$/u', '', $RV);
        }

        //STEP 4
        $m = preg_replace('/ь?$/u', '', $RV);
        if (strcmp($m, $RV) === 0) {
            $RV = preg_replace('/ейше?/u', '', $RV);
            $RV = preg_replace('/нн$/u', 'н', $RV);
        } else {
            $RV = $m;
        }

        $stem = $start . $RV;

        return $stem;
    }
}