<?php

namespace UkrainianStemmer;

/**
 * Algorithm provided by Dr Martin Porter
 * https://snowballstem.org/algorithms/russian/stemmer.html
 *
 */
class Stemmer
{
    const INFINITIVE = '/(ти|учи|ячи|вши|ши|ати|яти|ючи)$/u';
    const PERFECTIVEGROUND = '/(ив|ивши|ившись((?<=[ая])(в|вши|вшись)))$/u';
    const REFLEXIVE = '/(с[яьи])$/u';
    const ADJECTIVE = '/(ими|ій|ий|а|е|ова|ове|ів|є|їй|єє|еє|я|ім|ем|им|их|іх|ою|йми|іми|у|ю|ого|ому|ої)$/u';
    const PARTICIPLE = '/(ий|ого|ому|им|ім|а|ій|у|ою|і|их|йми)$/u';
    const VERB = '/(сь|ся|ив|ать|ять|у|ю|ав|али|учи|ячи|вши|ши|е|ме|ати|яти|є)$/u';
    const NOUN = '/(а|ев|ов|е|ями|ами|еи|и|ей|ой|ий|й|иям|ям|ием|ам|ом|о|у|ах|иях|ях|ь|ию|ью|ю|ия|ья|я|і|ові|ї|ею|єю|ою|є|еві|ем|єм|ів|їв|\'ю)$/u';
    const RVRE = '/^(.*?[аеиоуюяіїє])(.*)$/u';
    const DERIVATIONAL = '/[^аеиоуюяіїє][аеиоуюяіїє]+[^аеиоуюяіїє]+[аеиоуюяіїє].*сть?$/u';

    public static function stemWord(string $word): string
    {
        $stem = mb_strtolower($word);

        // check if infinitive
        $m = preg_replace(self::INFINITIVE, '', $word);
        if (strcmp($m, $word) !== 0) {
            return $word;
        }

        preg_match_all(self::RVRE, $stem, $p);
        if (!$p) {
            return $word;
        }

        if (empty($p[2]) || empty($p[1][0])) {
            return $word;
        }
        $start = $p[1][0];

        // RV is the region after the first vowel, or the end of the word if it contains no vowel.
        $RV = $p[2][0];

        /*
         * Step 1: Search for a PERFECTIVE GERUND ending. If one is found remove it, and that is then the end of step 1.
         * Otherwise try and remove a REFLEXIVE ending, and then search in turn for (1) an ADJECTIVAL, (2) a VERB or (3) a NOUN ending.
         * As soon as one of the endings (1) to (3) is found remove it, and terminate step 1.
         */
        $m = preg_replace(self::PERFECTIVEGROUND, '', $RV);
        if (strcmp($m, $RV) === 0) {
            $RV = preg_replace(self::REFLEXIVE, '', $RV);
            $m = preg_replace(self::ADJECTIVE, '', $RV);
            if (strcmp($m, $RV) === 0) {
                $RV = preg_replace(self::PARTICIPLE, '', $RV);
            } else {
                $RV = $m;
                $m = preg_replace(self::VERB, '', $RV);
                if (strcmp($m, $RV) === 0) {
                    $RV = preg_replace(self::NOUN, '', $RV);
                } else {
                    $RV = $m;
                }
            }
        } else {
            $RV = $m;
        }

        /*
         * Step 2: If the word ends with i, remove it.
         */
        $RV = preg_replace('/і$/u', '', $RV);

        /*
         * Step 3: Search for a DERIVATIONAL ending in R2 (i.e. the entire ending must lie in R2), and if one is found, remove it.
         */
        if (preg_match(self::DERIVATIONAL, $RV)) {
            $RV = preg_replace('/ість?$/u', '', $RV);
        }

        /*
         * Step 4: (1) Undouble н (n), or, (2) if the word ends with a SUPERLATIVE ending, remove it and undouble н (n),
         * or (3) if the word ends ь (') (soft sign) remove it.
         */
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