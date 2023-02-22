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
    const PERFECTIVE_GROUND = '/(ив|ивши|ившись((?<=[ая])(в|вши|вшись)))$/u';
    const REFLEXIVE = '/(с[яьи])$/u';
    const ADJECTIVE = '/(ими|ій|ий|а|е|ова|ове|ів|є|їй|єє|еє|я|ім|ем|им|их|іх|ою|йми|іми|у|ю|ого|ому|ої)$/u';
    const PARTICIPLE = '/(ий|ого|ому|им|ім|а|ій|у|ою|і|их|йми)$/u';
    const VERB_ENDING = '/(сь|ся|ив|ать|ять|у|ю|ав|али|учи|ячи|вши|ши|е|ме|ати|яти|є)$/u';
    const NOUN_ENDING = '/(а|ев|ов|е|ями|ами|еи|и|ей|ой|ий|й|иям|ям|ием|ам|ом|о|у|ах|иях|ях|ь|ию|ью|ю|ия|ья|я|і|ові|ї|ею|єю|ою|є|еві|ем|єм|ів|їв|\'ю)$/u';
    const FIRST_VOWEL = '/^(.*?[аеиоуюяіїє])(.*)$/u';
    const FIRST_NON_VOWEL = '/^(.*?[^аеиоуюяіїє])(.*)$/u';
    const DERIVATIONAL = '/(ість|іст)?$/u';

    public static function stemWord(string $originalWord): string
    {
        $originalWord = mb_strtolower($originalWord);

        if (preg_match(self::INFINITIVE, $originalWord)) {
            return $originalWord;
        }

        preg_match(self::FIRST_VOWEL, $originalWord, $matches);
        if (!$matches) {
            return $originalWord;
        }
        // RV is the region after the first vowel, or the end of the word if it contains no vowel.
        $RV = $matches[2];
        $baseWithFirstVowel = $matches[1];
        if (!mb_strlen($baseWithFirstVowel) || !mb_strlen($RV)) {
            return $originalWord;
        }

        // R1 is the region after the first non-vowel following a vowel, or the end of the word if there is no such non-vowel.
        // R2 is the region after the first non-vowel following a vowel in R1, or the end of the word if there is no such non-vowel.
        $R1 = false;
        $R2 = false;
        preg_match(self::FIRST_NON_VOWEL, $RV, $matches);
        if ($matches) {
            $R1 = $matches[2];
            preg_match(self::FIRST_NON_VOWEL, $R1, $matches);
            if ($matches) {
                $R2 = $matches[2];
            }
        }

        $RV = self::step1($RV);

        /*
         * Step 2: If the word ends with i, remove it.
         */
        $RV = preg_replace('/і$/u', '', $RV);

        /*
         * Step 3: Search for a DERIVATIONAL ending in R2 (i.e. the entire ending must lie in R2), and if one is found, remove it.
         */
        if ($R2 && preg_match(self::DERIVATIONAL, $R2)) {
            $RV = preg_replace(self::DERIVATIONAL, '', $RV);
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

        return $baseWithFirstVowel . $RV;
    }

    private static function step1($RV)
    {
        // Step 1: Search for a PERFECTIVE GERUND ending. If one is found remove it, and that is then the end of step 1.
        $replacedGro = preg_replace(self::PERFECTIVE_GROUND, '', $RV);
        if ($replacedGro && $replacedGro != $RV) {
            return $replacedGro;
        }
        // Otherwise, try and remove a REFLEXIVE ending, and then search in turn for (1) an ADJECTIVAL, (2) a VERB or (3) a NOUN ending.
        // As soon as one of the endings (1) to (3) is found remove it, and terminate step 1.
        $RV = preg_replace(self::REFLEXIVE, '', $RV);
        $replacedAdj = preg_replace(self::ADJECTIVE, '', $RV);
        if ($replacedAdj && $replacedAdj != $RV) {
            return preg_replace(self::PARTICIPLE, '', $replacedAdj);
        }
        $replacedVerb = preg_replace(self::VERB_ENDING, '', $RV);
        if ($replacedVerb && $replacedVerb != $RV) {
            return $replacedVerb;
        }
        $replacedNoun = preg_replace(self::NOUN_ENDING, '', $RV);
        if ($replacedNoun && $replacedNoun != $RV) {
            return $replacedNoun;
        }
        return $RV;
    }
}