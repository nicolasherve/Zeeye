<?php

namespace Zeeye\View\Helper;

use Zeeye\Util\Url\UrlGeneratorAccessor;

use Zeeye\Util\String\String;
use Zeeye\Util\String\Utf8String;
use Zeeye\Util\Url\Url;
use Zeeye\Util\Url\UrlException;

/**
 * Helper for basic HTML operations
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class HtmlHelper extends Helper {

	use UrlGeneratorAccessor;
	
    /**
     * List of available HTML tags for generated output
     * 
     * @var array
     */
    protected static $_tags = array(
        'csslink' => '<link rel="stylesheet" type="text/css" href="%s" />',
        'jslink' => '<script type="text/javascript" src="%s"></script>',
        'form' => '<form action="%s"%s>',
        'link' => '<a href="%s"%s>%s</a>',
        'mailto' => '<a href="%s" %s>%s</a>',
        'image' => '<img src="%s" %s/>',
        'input' => '<input %s/>',
        'textarea' => '<textarea %s>%s</textarea>',
        'select' => '<select %s>',
        'option' => '<option%s>%s</option>'
    );

    /**
     * Sanitizes the given URL
     * 
     * Uses this on untrusted URL.
     * If the URL is considered unsafe, the "#" char is returned
     * 
     * @param string $url the URL to sanitize
     * @return string
     */
    public function sanitizeUrl($url) {
        try {
            // Normalize the given URL
            $url = Url::normalize($url);
        } catch (UrlException $e) {
            return '#';
        }

        // If the URL is not considered safe
        if (!self::_isSafeUrl($url)) {
            return '#';
        }

        return $url;
    }

    /**
     * Indicates if the given URL can b e considered safe, based on the scheme
     * 
     * URLs whose scheme equals one of http, https, ftp, mailto are considered safe and are used as is
     * 
     * @param string $url the URL to test
     * @return boolean
     */
    private static function _isSafeUrl($url) {
        // Extract the scheme from the given URL
        $scheme = Url::extractScheme($url);

        // If there is no scheme
        if (empty($scheme)) {
            return false;
        }

        // Returns true if the extracted scheme is contained in a white list (http, https, ftp, mailto)
        return in_array(strtolower($scheme), array('http', 'https', 'ftp', 'mailto'));
    }

    /**
     * Generates the string corresponding to the given attributes
     * 
     * @param array $options the attributes we want to generate the corresponding HTML code
     * @return string
     */
    private static function _generatesAttributesString(array $options) {
        $attributes = array();
        foreach ($options as $key => $value) {
            $attributes[] = self::_generatesAttributeString($key, $value);
        }
        if (!empty($attributes)) {
            return ' ' . implode(' ', $attributes);
        }
        return '';
    }

    /**
     * Generates the string corresponding to the given attribute
     * 
     * @param string $key the attribute name
     * @param mixed $value the attribute value
     * @return string
     */
    private static function _generatesAttributeString($key, $value) {
        return $key . '="' . String::encodeHtmlAttributeValue($value) . '"';
    }

    /**
     * Encode the given string into HTML entities
     * 
     * @param string $string the string to encode
     * @return string
     */
    public function encodeHtml($string) {
        return String::encodeHtml($string);
    }

    /**
     * CSS encode the given string
     *
     * @param string $string the string to CSS encode
     * @return string
     */
    public function encodeCss($string) {
        return String::encodeCss($string);
    }

    /**
     * JavaScript encode the given string
     *
     * @param string $string the string to JavaScript encode
     * @return string
     */
    public function encodeJs($string) {
        return String::encodeJs($string);
    }

    /**
     * URL encode the given string
     *
     * @param string $string the string to URL encode
     * @return string
     */
    public function encodeUrl($string) {
        return String::encodeUrl($string);
    }

    /**
     * HTML encode the given attribute value
     *
     * @param string $string the attribute value to HTML encode
     * @return string
     */
    public function encodeHtmlAttributeValue($string) {
        return String::encodeHtmlAttributeValue($string);
    }

    /**
     * Returns the registered CSS links
     * 
     * @param string $groupName the group name associated to the files
     * @return array
     */
    public function getCssLinks($groupName = null) {
        return array_merge($this->_view->getCssLinks($groupName), $this->_view->getExtraCssLinks($groupName));
    }

    /**
     * Returns the registered Javascript links
     * 
     * @param string $groupName the group name associated to the files
     * @return string
     */
    public function getJsLinks($groupName = null) {
        return array_merge($this->_view->getJsLinks($groupName), $this->_view->getExtraJsLinks($groupName));
    }

    /**
     * Returns the tags corresponding to the registered CSS links
     * 
     * @param string $groupName the group name associated to the files
     * @return string
     */
    public function getCssLinksAsString($groupName = null) {
        $links = $this->getCssLinks($groupName);
        if (empty($links)) {
            return '';
        }
        $content = '';
        foreach ($links as $link) {
            $url = $link['url'];
            unset($link['url']);
            $content .= $this->css($url, $link);
        }
        return $content;
    }

    /**
     * Returns the tags corresponding to the registered Javascript links
     * 
     * @param string $groupName the group name associated to the files
     * @return string
     */
    public function getJsLinksAsString($groupName = null) {
        $links = $this->getJsLinks($groupName);
        if (empty($links)) {
            return '';
        }
        $content = '';
        foreach ($links as $link) {
            $url = $link['url'];
            unset($link['url']);
            $content .= $this->js($url, $link);
        }
        return $content;
    }

    /**
     * Returns the HTML CSS link tag corresponding to the given parameters
     * 
     * @param mixed $url the url to the CSS file
     * @param array $options HTML attributes to apply to the link tag
     * @return string
     */
    public function css($url, array $options = array()) {
        // Generate the URL as a string
        $urlString = $this->url($url);

        return sprintf(self::$_tags['csslink'], $this->encodeHtmlAttributeValue($urlString), self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML Javascript link tag corresponding to the given parameters
     * 
     * @param mixed $url the url to the Javascript file
     * @param array $options HTML attributes to apply to the link tag
     * @return string
     */
    public function js($url, array $options = array()) {
        // Generate the URL as a string
        $urlString = $this->url($url);

        return sprintf(self::$_tags['jslink'], $this->encodeHtmlAttributeValue($urlString), self::_generatesAttributesString($options));
    }

    /**
     * Registers the given URL as a link to a CSS file
     * 
     * @param mixed $url the url to the CSS file
     * @param array $options HTML attributes to apply to the link tag
     * @param string $groupName the group name associated to the file
     */
    public function addCssLink($url, array $options = array(), $groupName = null) {
        // Generate the URL as a string
        $urlString = $this->url($url);

        $link = array_merge(array('url' => $urlString), $options);

        $this->_view->addCssLink($link, $groupName);
    }

    /**
     * Registers the given URL as a link to a Javascript file
     * 
     * @param mixed $url the url to the Javascript file
     * @param array $options HTML attributes to apply to the link tag
     * @param string $groupName the group name associated to the file
     */
    public function addJsLink($url, array $options = array(), $groupName = null) {
        // Generate the URL as a string
        $urlString = $this->url($url);

        $link = array_merge(array('url' => $urlString), $options);

        $this->_view->addJsLink($link, $groupName);
    }

    public function form($id, $url, array $options = array()) {
        // Generate the URL as a string
        $urlString = $this->url($url);

        $options['id'] = $id;
        if (!isset($options['name'])) {
            $options['name'] = $id;
        }

        return sprintf(self::$_tags['form'], $urlString, self::_generatesAttributesString($options));
    }

    public function formEnd() {
        return '</form>';
    }

    /**
     * Returns the HTML link tag corresponding to the given parameters
     * 
     * @param mixed $url the Url of the link
     * @param string $label the label of the link
     * @param array $options HTML attributes to apply to the link tag
     * @return string
     */
    public function link($url, $label = null, array $options = array()) {
        // Generate the URL as a string
        $urlString = $this->url($url);

        // If the given label is null
        if (empty($label)) {
            $label = $urlString;
        }

        return sprintf(self::$_tags['link'], $this->encodeHtmlAttributeValue($urlString), self::_generatesAttributesString($options), $this->encodeHtml($label));
    }

    /**
     * Returns the HTML img tag corresponding to the given parameters
     * 
     * @param Url|string $url the url of the image
     * @param array $options HTML attributes to apply to the image tag
     * @return string
     */
    public function image($url, array $options = array()) {
        // Generate the URL as a string
        $urlString = $this->url($url);

        // If there is no alt attribute
        if (!isset($options['alt'])) {
            if (isset($options['title'])) {
                $options['alt'] = $options['title'];
            } else {
                $options['alt'] = 'Image';
            }
        }

        return sprintf(self::$_tags['image'], $this->encodeHtmlAttributeValue($urlString), self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML mailto tag corresponding to the given parameters
     * 
     * The email address will be encoded.
     * 
     * @param string $address the email address
     * @param string $label the label of the link
     * @param array $options HTML attributes to apply to the mailto tag
     * @return string
     */
    public function mailto($address, $label = null, array $options = array()) {
        // Encode the mail address
        $encodedAddress = $this->obfuscate($address);

        // Generate the encoded label
        $encodedLabel = $encodedAddress;
        if (!empty($label)) {
            $encodedLabel = $this->obfuscate($label);
        }

        // Prepend "mailto:" to the address
        $encodedMailto = $this->obfuscate('mailto:');

        return sprintf(self::$_tags['link'], $encodedMailto . $encodedAddress, self::_generatesAttributesString($options), $encodedLabel);
    }

    /**
     * Returns the HTML input tag of type checkbox corresponding to the given parameters
     * 
     * @param string $name name of the input
     * param boolean $isChecked indicates whether the input is checked or not
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function inputCheckbox($name, $isChecked = false, array $options = array()) {
        $options['type'] = 'checkbox';
        $options['name'] = $name;
        if (!isset($options['id'])) {
            $options['id'] = $name;
        }
        if ($isChecked) {
            $options['checked'] = true;
        }
        return sprintf(self::$_tags['input'], self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML input tag of type radio corresponding to the given parameters
     * 
     * @param string $name name of the input
     * @param string $value the value associated with the input tag
     * @param mixed $selectedValue indicates if the input is checked
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function inputRadio($name, $value, $selectedValue = false, array $options = array()) {
        $options['type'] = 'radio';
        $options['name'] = $name;
        $options['value'] = $value;
        if (is_bool($selectedValue) && $selectedValue) {
            $options['checked'] = 'checked';
        } elseif ($value == $selectedValue) {
            $options['checked'] = 'checked';
        }
        return sprintf(self::$_tags['input'], self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML input tag of type number corresponding to the given parameters
     * 
     * @param string $name name of the input
     * @param string $value value of the input
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function inputNumber($name, $value = '', array $options = array()) {
        $options['type'] = 'number';
        $options['name'] = $name;
        if (!isset($options['id'])) {
            $options['id'] = $name;
        }
        $options['value'] = $value;
        return sprintf(self::$_tags['input'], self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML input tag of type email corresponding to the given parameters
     * 
     * @param string $name name of the input
     * @param string $value value of the input
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function inputEmail($name, $value = '', array $options = array()) {
        $options['type'] = 'email';
        $options['name'] = $name;
        if (!isset($options['id'])) {
            $options['id'] = $name;
        }
        $options['value'] = $value;
        return sprintf(self::$_tags['input'], self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML input tag of type submit corresponding to the given parameters
     * 
     * @param string $label label of the button
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function inputSubmit($label, array $options = array()) {
        $options['type'] = 'submit';
        $options['value'] = $label;
        return sprintf(self::$_tags['input'], self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML input tag of type button corresponding to the given parameters
     * 
     * @param string $label label of the button
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function inputButton($label, array $options = array()) {
        $options['type'] = 'button';
        $options['value'] = $label;
        return sprintf(self::$_tags['input'], self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML input tag of type text corresponding to the given parameters
     * 
     * @param string $name name of the input
     * @param string $value value of the input
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function inputText($name, $value = '', array $options = array()) {
        $options['type'] = 'text';
        $options['name'] = $name;
        if (!isset($options['id'])) {
            $options['id'] = $name;
        }
        $options['value'] = $value;
        return sprintf(self::$_tags['input'], self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML input tag of type password corresponding to the given parameters
     * 
     * @param string $name name of the input
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function inputPassword($name, array $options = array()) {
        $options['type'] = 'password';
        $options['name'] = $name;
        if (!isset($options['id'])) {
            $options['id'] = $name;
        }
        return sprintf(self::$_tags['input'], self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML textarea tag corresponding to the given parameters
     * 
     * @param string $name name of the textarea
     * @param string $value value of the textarea
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function textarea($name, $value = '', array $options = array()) {
        $options['name'] = $name;
        if (!isset($options['id'])) {
            $options['id'] = $name;
        }

        return sprintf(self::$_tags['textarea'], self::_generatesAttributesString($options), $this->encodeHtml($value));
    }

    /**
     * Returns the HTML input tag of type hidden corresponding to the given parameters
     * 
     * @param string $name name of the input
     * @param string $value value of the input
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function inputHidden($name, $value = 1, array $options = array()) {
        $options['type'] = 'hidden';
        $options['value'] = $value;
        $options['name'] = $name;
        if (!isset($options['id'])) {
            $options['id'] = $name;
        }
        return sprintf(self::$_tags['input'], self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML input tag of type file corresponding to the given parameters
     * 
     * @param string $name name of the input
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function inputFile($name, array $options = array()) {
        $options['type'] = 'file';
        $options['name'] = $name;
        if (!isset($options['id'])) {
            $options['id'] = $name;
        }
        return sprintf(self::$_tags['input'], self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML select tag corresponding to the given parameters
     * 
     * @param string $name name of the tag
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function select($name, array $options = array()) {
        $options['name'] = $name;
        if (!isset($options['id'])) {
            $options['id'] = $name;
        }
        return sprintf(self::$_tags['select'], self::_generatesAttributesString($options));
    }

    /**
     * Returns the HTML select end tag
     * 
     * @return string
     */
    public function selectEnd() {
        return '</select>';
    }

    /**
     * Returns the HTML option tag corresponding to the given parameters
     * 
     * @param string $label the label of the option
     * @param string $value value of the option
     * @param mixed $selectedValue value of the selected option
     * @param array $options HTML attributes to apply to the tag
     * @return string
     */
    public function option($label, $value = null, $selectedValue = null, array $options = array()) {
        $options = array();
        if (isset($value)) {
            $options['value'] = $value;
        } else {
            $options['value'] = $label;
        }

        if (isset($selectedValue)) {
            if ($value == $selectedValue) {
                $options['selected'] = 'selected';
            }
        }

        return sprintf(self::$_tags['option'], self::_generatesAttributesString($options), $this->encodeHtml($label));
    }

    /**
     * Returns the given string by replacing the \n characters into <br> tags
     * 
     * @param string $string the string to replace
     * @return string
     */
    public function nl2br($string) {
        return nl2br($string, true);
    }

    /**
     * Returns the substring truncated with maximum length
     * 
     * @param string $string the string to truncate eventually
     * @param integer $length the maximum length of the string
     * @param string $end some string to append after the string is truncated
     * @return string the truncated string
     */
    public function truncate($string, $length=30, $end = '...') {
        // If the given text contains UTF-8 characters
        if (Utf8String::isUtf8($string)) {
            if (Utf8String::length($string) <= $length) {
                return $string;
            }
            return Utf8String::substr($string, 0, $length) . $end;
        }

        if (strlen($string) <= $length) {
            return $string;
        }

        return substr($string, 0, $length) . $end;
    }

    public function email($address) {
        return $this->obfuscate($address);
    }

    public function obfuscate($string) {
        $safe = '';
        foreach (str_split($string) as $char) {
            switch (rand(1, 2)) {

                case 1:
                    // HTML Decimal code
                    $safe .= String::encodeAsciiCharToHtmlDec($char);
                    break;

                case 2:
                    // HTML Hexadecimal code
                    $safe .= String::encodeAsciiCharToHtmlHex($char);
                    break;

// 				case 3:
                // HTML Unicode
// 					$safe .= String::encodeAsciiCharToHtmlUnicode($char);
// 					break;
            }
        }

        return $safe;
    }

    public function startStrip() {
        ob_start();
    }

    public function endStrip() {
        $content = ob_get_clean();

        echo preg_replace('/\s+/', ' ', $content);
    }

}
