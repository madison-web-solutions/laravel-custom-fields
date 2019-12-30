<?php

namespace MadisonSolutions\LCF;

class PasswordStrengthTester
{
    // Return the base10 log of the number of possibilities for a string 'like' $value
    // By 'like', I mean a string with the same length, taken from the same set of characters
    // If $value has an upper case character, then we assume the possible strings can contain any upper case character
    // If $value has a digit then we assume the possible strings can contain any digit, etc
    // The base10 log is returned to avoid issues with overflow if the numbers get too big
    protected static function stringComplexity(string $value)
    {
        $base = 1;
        if (preg_match('/[A-Z]/', $value)) {
            // string has upper case chars
            $base += 26;
        }
        if (preg_match('/[a-z]/', $value)) {
            // string has lower case chars
            $base += 26;
        }
        if (preg_match('/[0-9]/', $value)) {
            // string has digits
            $base += 10;
        }
        if (preg_match('/[-_ !]/', $value)) {
            // string has obvious special chars
            $base += 4;
        }
        if (preg_match('/[^-A-Za-z0-9_ !]/', $value)) {
            // string has other special chars
            $base += 20;
        }
        if ($base == 0) {
            return 0;
        }

        $len = mb_strlen($value);
        return $len * log10($base);
    }

    // Replace commonly used leet characters with their lowercase latin equivalents
    // Used before comparing with the dictionary
    protected static function normalise(string $value)
    {
        $leet = [
            ['!', '@', '$', '+', '1', '3', '4', '5', '6', '9', '0'],
            ['i', 'a', 's', 't', 'l', 'e', 'a', 's', 'b', 'g', 'o'],
        ];
        return str_replace($leet[0], $leet[1], mb_strtolower($value));
    }

    // Return a scale factor between 0 and 1 which the password strength should be multiplied by
    // to take into account close matches to dictionary words
    protected static function dictionaryFactor(string $normalised)
    {
        // Make sure string isn't a dictionary word
        $score = 1;
        $fh = fopen(dirname(__DIR__).'/dictionary.txt', 'r');
        if (!$fh) {
            throw new \Exception("Failed to open dictionary.txt for password strength checking");
        }
        while (($line = fgets($fh)) !== false) {
            switch (levenshtein($normalised, trim($line))) {
                case 0:
                    // Exactly the same as a dictionary word
                    $score = min($score, 0.05);
                    break;
                case 1:
                    // 1 letter away from a dictionary word
                    $score = min($score, 0.2);
                    break;
                case 2:
                    // 2 letters away from a dictionary word
                    $score = min($score, 0.6);
                    break;
                case 3:
                    // 3 letters away from a dictionary word
                    $score = min($score, 0.9);
                    break;
                default:
                    // fine
                    break;
            }
        }
        fclose($fh);
        return $score;
    }

    // Return a score between 0 and 5 for the strength of the password
    // 0-2 very weak and should usually reject
    // 3 not great
    // 4 pretty good
    // 5 very strong
    public static function score(string $password)
    {
        $score = self::stringComplexity($password) * self::dictionaryFactor(self::normalise($password));
        $scaled = floor(($score - 2) / 4);
        return max(0, min(5, $scaled));
    }
}
