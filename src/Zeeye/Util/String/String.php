<?php

namespace Zeeye\Util\String;

use Zeeye\App\App;
use Zeeye\Util\Url\Url;

/**
 * Provide operations for basic strings manipulation
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class String {

    /**
     * Encode HTML string 
     * 
     * @param string $string the string to HTML encode
     * @param string $charset the charset of encoding
     * @return string the HTML encoded string
     */
    public static function encodeHtml($string, $charset = null) {
        if (!isset($charset)) {
            $charset = App::getInstance()->getAppConfiguration()->getDefaultCharset();
        }

        // & --> &amp;
        // < --> &lt;
        // > --> &gt;
        // " --> &quot;
        // ' --> &#x27;
        $escaped = htmlspecialchars($string, ENT_QUOTES, $charset);

        // / --> &#x2F;
        return str_replace('/', '&#x2F;', $escaped);
    }

    /**
     * Decode HTML entities
     * 
     * @param string $string the string to HTML decode
     * @param string $charset the charset of decoding
     * @return string the HTML decoded string
     */
    public static function decodeHtml($string, $charset = null) {
        if (!isset($charset)) {
            $charset = App::getInstance()->getAppConfiguration()->getDefaultCharset();
        }

        // &amp; --> &
        // &lt; --> <
        // &gt; --> >
        // &quot; --> "
        // &#x27; --> '
        $decoded = htmlspecialchars_decode($string, ENT_QUOTES, $charset);

        // &#x2F; --> /
        return str_replace('&#x2F;', '/', $decoded);
    }

    /**
     * HTML encode the given attribute value
     * 
     * Escape all ASCII and non-alphanumeric characters with the HTML Entity &#xHH; format, including spaces. (HH = Hex Value) 
     * 
     * @param string $value the attribute value to HTML encode
     * @return string the HTML encoded attribute value
     */
    public static function encodeHtmlAttributeValue($value) {
        return preg_replace_callback('/(\W)/', function($matches) {
            return String::encodeAsciiCharToHtmlHex($matches[0]);
        }, $value);
    }

    /**
     * JavaScript encode the given string
     * 
     * Escape all ASCII and non-alphanumeric characters with the \uAAAA unicode escaping format (A = Integer). 
     * 
     * @param string $string the string to JavaScript encode
     * @return string the JavaScript encoded string
     */
    public static function encodeJs($string) {
        return preg_replace_callback('/(\W)/', function($matches) {
            return String::encodeAsciiCharToHtmlUnicode($matches[0]);
        }, $string);
    }

    /**
     * CSS encode the given string
     * 
     * Escape all ASCII and non-alphanumeric characters with the \uAAAA unicode escaping format (A = Integer). 
     * 
     * @param string $string the string to CSS encode
     * @return string the CSS encoded string
     */
    public static function encodeCss($string) {
        return preg_replace_callback('/(\W)/', function($matches) {
            return String::encodeAsciiCharToHtmlUnicode($matches[0]);
        }, $string);
    }

    /**
     * URL-encode the given string
     * 
     * @param string $string the string to URL encode
     * @return string the URL-encoded string
     */
    public static function encodeUrl($string) {
        return Url::encode($string);
    }

    /**
     * Encode the ASCII characters of the given string into the HTML Unicode format (\uAAAA)
     * 
     * @param string $string the string to encode
     * @return string the encoded string 
     */
    public static function encodeAsciiCharsOfStringToHtmlUnicode($string) {
        if (strlen($string) > 1) {
            return preg_replace_callback('/([\x00-\x7F])/', function($matches) {
                return String::encodeAsciiCharToHtmlUnicode($matches[0]);
            }, $string);
        } elseif (self::isAscii($string)) {
            return self::encodeAsciiCharToHtmlUnicode($string);
        }
        return $string;
    }

    /**
     * Encode the given ASCII character into the HTML Unicode format (\uAAAA)
     * 
     * @param string $char the ASCII character to encode
     * @return string the HTML Unicode encoded ASCII character
     */
    public static function encodeAsciiCharToHtmlUnicode($char) {
        if (self::isAscii($char)) {
            return '\u00' . bin2hex($char);
        }
        return $char;
    }

    /**
     * Encode the ASCII characters of the given string into the HTML hexadecimal format (&#xAA;)
     * 
     * @param string $string the string to encode
     * @return string the encoded string
     */
    public static function encodeAsciiCharsOfStringToHtmlHex($string) {
        if (strlen($string) > 1) {
            return preg_replace_callback('/([\x00-\x7F])/', function($matches) {
                return String::encodeAsciiCharToHtmlHex($matches[0]);
            }, $string);
        }
        return self::encodeAsciiCharToHtmlHex($string);
    }

    /**
     * Encode the given ASCII character into the HTML hexadecimal format (&#xAA;)
     * 
     * @param string $char the ASCII character to encode
     * @return string the HTML hexadecimal encoded ASCII character
     */
    public static function encodeAsciiCharToHtmlHex($char) {
        if (self::isAscii($char)) {
            return '&#x' . bin2hex($char) . ';';
        }
        return $char;
    }

    /**
     * Encode the ASCII characters of the given string into the HTML decimal format (&#AA;)
     *
     * @param string $string the string to encode
     * @return string the encoded string
     */
    public static function encodeAsciiCharsOfStringToHtmlDec($string) {
        if (strlen($string) > 1) {
            return preg_replace_callback('/([\x00-\x7F])/', function($matches) {
                return String::encodeAsciiCharToHtmlDec($matches[0]);
            }, $string);
        }
        return self::encodeAsciiCharToHtmlDec($string);
    }

    /**
     * Encode the given ASCII character into the HTML decimal format (&#AA;)
     *
     * @param string $char the ASCII character to encode
     * @return string the HTML decimal encoded ASCII character
     */
    public static function encodeAsciiCharToHtmlDec($char) {
        if (self::isAscii($char)) {
            return '&#' . ord($char) . ';';
        }
        return $char;
    }

    /**
     * Indicates if the given string contains only ASCII characters
     * 
     * @param string $string the string to check
     * @return boolean
     */
    public static function isAscii($string) {
        if (Utf8String::isMbStringEnabled()) {
            return mb_detect_encoding($string, 'ASCII') !== false;
        }

        return !preg_match('/[^\x00-\x7F]/', $string);
    }

}
