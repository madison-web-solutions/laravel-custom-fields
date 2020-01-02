<?php

namespace MadisonSolutions\LCF;

use DOMDocument;
use DOMNode;

/**
 * Class used to filter a string of HTML so that it only contains whitelisted elements and attributes
 *
 * Intended to be used by the HTMLField to prevent saving any invalid or unsafe HTML
 */
class RestrictedHTMLFragment
{
    /**
     * Specifications for the allowed elements, cached for performance
     *
     * @var array
     */
    protected static $ele_specs;

    /**
     * Specifications for the allowed attributes, cached for performance
     *
     * @var array
     */
    protected static $allowed_attrs;

    /**
     * Internal DOMDocument object constructed from the input HTML string
     *
     * @var DOMDocument
     */
    protected $doc;

    /**
     * Array of warning messages
     *
     * @var array
     */
    public $warnings;

    /**
     * Array of error messages
     *
     * @var array
     */
    public $errors;

    /**
     * Filter and sanitize a fragment of HTML
     *
     * Returns the filtered and sanitized HTML string.
     * Will only contain whitelisted elements and attributes.
     * Note that the input string is expected to be a fragment from the body of an HTML document,
     * not the entire document. IE it should not include doctype, html, head, body elements etc.
     *
     * @param string $fragment_html String of (possibly untrusted) HTML
     * @return string The sanitized HTML
     */
    public function toRestrictedHtml($fragment_html)
    {
        $this->warnings = [];
        $this->errors = [];

        // Create DOMDocument object from the HTML string
        $this->doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $result = $this->doc->loadHTML('<!doctype html><html><meta charset="utf-8"><head></head><body>' . $fragment_html . '</body></html>');
        if (! $result) {
            $this->errors[] = 'Failed to parse html';
            return '';
        }

        $out = '';
        foreach ($this->doc->documentElement->childNodes as $node) {
            if ($node->nodeName == 'body') {
                foreach ($node->childNodes as $node) {
                    $out .= $this->nodeToHtml($node);
                }
                break;
            }
        }

        return $out;
    }

    /**
     * Return sanitized HTML for a single DOM node
     *
     * Returns an empty string if the node is not allowed at it's position in the DOM.
     *
     * @param DOMNode $node the DOM node
     * @return string The sanitized HTML
     */
    protected function nodeToHtml(DOMNode $node)
    {
        switch ($node->nodeType) {
            case XML_ELEMENT_NODE:
                if ($this->isAllowedEle($node)) {
                    return $this->eleToHtml($node);
                } else {
                    $this->warnings[] = "Skipping disallowed <{$node->nodeName}> element (child of <{$node->parentNode->nodeName}>)";
                    return '';
                }
            case XML_TEXT_NODE:
                if ($this->isEle($node->parentNode->nodeName, 'inline_children')) {
                    return htmlspecialchars($node->data);
                } else {
                    $trimmed = trim($node->data);
                    if (! empty($trimmed)) {
                        if ($node->parentNode->nodeName == 'body') {
                            // raw text as direct child of body - we'll wrap it in a <p> tag and carry on
                            $p = $this->doc->createElement('p');
                            $node->parentNode->replaceChild($p, $node);
                            $p->appendChild($node);
                            return $this->nodeToHtml($p);
                        }
                        $this->warnings[] = "Skipping text '{$trimmed}' from non-inline element <{$node->parentNode->nodeName}>";
                    }
                    return '';
                }
            case XML_COMMENT_NODE:
                // Skip comments
                $this->warnings[] = "Skipping comment '{$node->data}'";
                return '';
            default:
                // Shouldn't get here
                $this->errors[] = "Unexpected node type '{$node->nodeType}'";
                return '';
        }
    }

    /**
     * Return sanitized HTML for a XML_ELEMENT_NODE node
     *
     * Generate sanitized HTML for a DOMNode element which has already been confirmed to be of type XML_ELEMENT_NODE.
     * The output will only contain permitted attributes and HTML for permitted child nodes
     *
     * @param DOMNode $node the XML_ELEMENT_NODE DOM node
     * @return string The sanitized HTML
     */
    protected function eleToHtml(DOMNode $ele)
    {
        $atts_html = [];
        foreach ($ele->attributes as $attr) {
            if ($this->isAllowedAttr($attr->nodeName, $ele->nodeName)) {
                $atts_html[] = $this->attrToHtml($attr);
            } else {
                $this->warnings[] = "Skipping disallowed attribute '{$attr->nodeName}' from <{$ele->nodeName}>";
            }
        }
        $atts_html = empty($atts_html) ? '' : (' ' . implode(' ', $atts_html));

        if ($this->isEle($ele->nodeName, 'self_closing')) {
            // If it's a self-closing element, don't generate any inner HTML
            $opening_tag = '<' . $ele->nodeName . $atts_html . ' />';
            $inner_html = '';
            $closing_tag = '';
        } else {
            $opening_tag = '<' . $ele->nodeName . $atts_html . '>';
            $inner_html = [];
            foreach ($ele->childNodes as $child) {
                // Note nodeToHtml will return an empty string if the child node is not permitted
                $inner_html[] = $this->nodeToHtml($child);
            }
            $inner_html = implode("", $inner_html);
            $closing_tag = '</' . $ele->nodeName . '>';
        }

        return $opening_tag . $inner_html . $closing_tag;
    }

    /**
     * Get the HTML for an element attribute
     *
     * Generate sanitized HTML for an element attribute.
     * IE a DOMNode element which has already been confirmed to be of type XML_ATTRIBUTE_NODE.
     * Returns a string of the form name="value" where the attribute value is escaped for HTML
     *
     * @param DOMNode $node the XML_ELEMENT_NODE DOM node
     * @return string The sanitized HTML
     */
    protected function attrToHtml(DOMNode $attr)
    {
        return $attr->nodeName . '="' . htmlspecialchars($attr->value, ENT_QUOTES) . '"';
    }

    /**
     * Get the spec for a particular element type
     *
     * @param string $node_name The HTML element name, in lower case, eg h1
     * @return array Array of information about how the element should be treated.
     */
    protected function getEleSpec(string $node_name)
    {
        if (is_null(self::$ele_specs)) {
            $defaults = [
                'heading' => false,          // is this element a heading
                'block' => false,            // is this a block level element
                'inline' => false,           // is this an inline element
                'li' => false,               // is this a li element (which can be a direct child of a ol or ul)
                'fig' => false,              // is this a table element (which can be a direct child of a figure)
                'tbody' => false,            // is this a tbody or thead element (which can be a direct child of a table)
                'tr' => false,               // is this a tr element (which can be a direct child of a tbody or thead)
                'td' => false,               // is this a td of th element (which can be a direct child of a tr)
                'block_children' => false,   // is this element allowed to have block children
                'inline_children' => false,  // is this element allowed to have inline children
                'li_children' => false,      // is this element allowed to have li children
                'fig_children' => false,     // is this element allowed to have fig children
                'tbody_children' => false,   // is this element allowed to have tbody (or thead) children
                'tr_children' => false,      // is this element allowed to have tr children
                'td_children' => false,      // is this element allowed to have td children
                'self_closing' => false,     // is this a self-closing element (eg <br />)
            ];

            // Define the specs for the elements we're choosing to allow
            // Note all array values are booleans
            self::$ele_specs = [
                'body' => ['block_children' => true],
                'h1' => ['heading' => true, 'block' => true, 'inline_children' => true],
                'h2' => ['heading' => true, 'block' => true, 'inline_children' => true],
                'h3' => ['heading' => true, 'block' => true, 'inline_children' => true],
                'h4' => ['heading' => true, 'block' => true, 'inline_children' => true],
                'h5' => ['heading' => true, 'block' => true, 'inline_children' => true],
                'h6' => ['heading' => true, 'block' => true, 'inline_children' => true],
                'p' => ['block' => true, 'inline_children' => true],
                'blockquote' => ['block' => true, 'block_children' => true],
                'ol' => ['block' => true, 'li_children' => true],
                'ul' => ['block' => true, 'li_children' => true],
                'figure' => ['block' => true, 'fig_children' => true],
                'li' => ['li' => true, 'block_children' => true, 'inline_children' => true],
                'table' => ['fig' => true, 'tbody_children' => true],
                'tbody' => ['tbody' => true, 'tr_children' => true],
                'thead' => ['tbody' => true, 'tr_children' => true],
                'tr' => ['tr' => true, 'td_children' => true],
                'td' => ['td' => true, 'block_children' => true, 'inline_children' => true],
                'th' => ['td' => true, 'block_children' => true, 'inline_children' => true],
                'hr' => ['block' => true, 'self_closing' => true],
                'a' => ['inline' => true,  'inline_children' => true],
                'span' => ['inline' => true, 'inline_children' => true],
                'strong' => ['inline' => true, 'inline_children' => true],
                'u' => ['inline' => true, 'inline_children' => true],
                'em' => ['inline' => true, 'inline_children' => true],
                'b' => ['inline' => true, 'inline_children' => true],
                'i' => ['inline' => true, 'inline_children' => true],
                's' => ['inline' => true, 'inline_children' => true],
                'del' => ['inline' => true, 'inline_children' => true],
                'code' => ['inline' => true, 'inline_children' => true],
                'br' => ['inline' => true, 'self_closing' => true],
            ];

            foreach (self::$ele_specs as &$spec) {
                $spec = $spec + $defaults;
            }
        }
        return self::$ele_specs[$node_name] ?? null;
    }

    /**
     * Determine whether the given element type has the given property in its spec
     *
     * Note if the element name is not known, or the property is not defined in the spec, false is returned by default.
     *
     * @param string $node_name The element name in lower case
     * @param string $property The name of a property in the element spec, as defined in getEleSpec()
     * @return bool Whether the given property is true for the given element
     */
    protected function isEle(string $node_name, string $property)
    {
        $spec = $this->getEleSpec($node_name);
        return (bool) ($spec ? $spec[$property] : false);
    }

    /**
     * Determine whether the given element is allowed in the output
     *
     * @param DOMNode $node the XML_ELEMENT_NODE DOM node
     * @return bool Whether the element is allowed in the output
     */
    protected function isAllowedEle(\DomNode $node)
    {
        $spec = $this->getEleSpec($node->nodeName);
        $parent_spec = $this->getEleSpec($node->parentNode->nodeName);

        if (is_null($spec) || is_null($parent_spec)) {
            return false;
        }

        if ($spec['block'] && ! $parent_spec['block_children']) {
            return false;
        }
        if ($spec['inline'] && ! $parent_spec['inline_children']) {
            return false;
        }
        if ($spec['li'] && ! $parent_spec['li_children']) {
            return false;
        }
        if ($spec['tbody'] && ! $parent_spec['tbody_children']) {
            return false;
        }
        if ($spec['tr'] && ! $parent_spec['tr_children']) {
            return false;
        }
        if ($spec['td'] && ! $parent_spec['td_children']) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the given attribute is allowed in the output
     *
     * @param string $attr_name The attribute name
     * @param string $node_name The name of the node which this attribute is defined on
     * @return bool Whether the attribute is allowed in the output
     */
    protected function isAllowedAttr(string $attr_name, string $node_name)
    {
        if (is_null(self::$allowed_attrs)) {
            self::$allowed_attrs = [
                'class',
                'img:src',
                'a:href',
                'a:target',
                'td:colspan',
                'th:colspan',
                'td:rowspan',
                'th:rowspan',
            ];
        }
        return in_array($attr_name, self::$allowed_attrs) || in_array("{$node_name}:{$attr_name}", self::$allowed_attrs);
    }
}
