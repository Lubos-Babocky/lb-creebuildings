<?php

namespace LB\CreeBuildings\Utils;

/**
 * Description of GeneralUtility
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class GeneralUtility
{

    public static function GetMultiArrayValue(
        mixed $inputArray,
        string $keys = '',
        mixed $defaultValue = ''
    ): mixed
    {
        if (!is_array($inputArray)) {
            return $defaultValue;
        }
        $separatedKeys = explode('.', $keys);
        $currentKey = array_shift($separatedKeys);
        if (empty($separatedKeys) && array_key_exists($currentKey, $inputArray)) {
            return $inputArray[$currentKey];
        } elseif (!empty($separatedKeys) && array_key_exists($currentKey, $inputArray)) {
            return self::GetMultiArrayValue($inputArray[$currentKey], implode('.', $separatedKeys));
        } else {
            return $defaultValue;
        }
    }

    public static function TrimExplode(
        string $input,
        string $separator,
        string $characters = " \n\r\t\v\x00"
    ): array
    {
        $parts = explode($separator, $input);
        array_walk($parts, function (&$part) use ($characters) {
            $part = trim($part, $characters);
        });
        return $parts;
    }

    public static function ExtractUrlParts(
        string $url
    ): array
    {
        $urlParts = parse_url($url);
        if (array_key_exists('query', $urlParts) && !empty($urlParts['query'])) {
            $queryData = [];
            parse_str($urlParts['query'], $queryData);
            $urlParts['query'] = $queryData;
        }
        return $urlParts;
    }

    public static function Slugify(
        string $input
    ): string
    {
        return trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower($input)) ?? '', '-');
    }

    /**
     * Searches associative array for value with highest value in specified column
     * function supports array with columns without specified key
     * @param array $inputArray The input associative array.
     * @param string $key The key to search for the highest value.
     * @return array The element with the highest value in the specified key. Returns an empty array if no valid element is found.
     */
    public static function FindMaxInAssocArray(
        array $inputArray,
        string $key
    ): array
    {
        $max = null;
        foreach ($inputArray as $val) {
            if (!(is_array($val) && array_key_exists($key, $val))) {
                continue;   //[L:] ignore this value
            }
            if ($max === null || $val[$key] > $max[$key]) {
                $max = $val;
            }
        }
        return $max ?? [];
    }

    /**
     * Returns everything before first "?"
     * @param string $inputUrl
     * @return string
     */
    public static function RemoveQueryString(
        string $inputUrl
    ): string
    {
        $urlParts = explode('?', $inputUrl);
        return $urlParts[0];
    }

    public static function PrintAsJSON($arguments): never
    {
        ob_clean();
        header('Content-Type: application/json');
        die(json_encode($arguments, JSON_PRETTY_PRINT));
    }
}
