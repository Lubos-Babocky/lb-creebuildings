<?php

namespace LB\CreeBuildings\Utils;

/**
 * Description of GeneralUtility
 *
 * @author Lubos Babocky <babocky@gmail.com>
 */
class GeneralUtility {

    public static function GetMultiArrayValue(mixed $inputArray, string $keys = '', mixed $defaultValue = ''): mixed {
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

    public static function TrimExplode(string $input, string $separator, string $characters = " \n\r\t\v\x00"): array {
        $parts = explode($separator, $input);
        array_walk($parts, function (&$part) use ($characters) {
            $part = trim($part, $characters);
        });
        return $parts;
    }

    public static function ExtractUrlParts(string $url): array {
        $urlParts = parse_url($url);
        if (array_key_exists('query', $urlParts) && !empty($urlParts['query'])) {
            $queryData = [];
            parse_str($urlParts['query'], $queryData);
            $urlParts['query'] = $queryData;
        }
        return $urlParts;
    }

    public static function Slugify(string $input): string {
        return trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower($input)) ?? '', '-');
    }
}
