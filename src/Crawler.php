<?php

namespace Procket\Phquery;

use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler as BaseCrawler;

class Crawler extends BaseCrawler
{
    /**
     * Load HTML or XML content for query
     *
     * ```
     * The options default values are as follows:
     * [
     *      // content encoding
     *      'encoding' => 'UTF-8',
     *      // Whether the content is XML, automatically detected by default
     *      'xml_mode' => null,
     *      // LibXMl parameter options when content is xml
     *      'libxml_options' => LIBXML_NONET,
     * ]
     * ```
     *
     * @param mixed $content HTML or XML content
     * @param array $options Options
     * @return static
     */
    public static function query(mixed $content, array $options = []): Crawler
    {
        $optionsDefault = [
            'encoding' => 'UTF-8',
            'xml_mode' => null,
            'libxml_options' => LIBXML_NONET
        ];
        $options = array_replace($optionsDefault, $options);

        if ($options['xml_mode'] === null) {
            $crawler = new static($content);
        } else {
            $crawler = new static();
            if ($options['xml_mode'] === true) {
                $crawler->addXmlContent($content, $options['encoding'], $options['libxml_options']);
            } else {
                $crawler->addHtmlContent($content, $options['encoding']);
            }
        }

        return $crawler;
    }

    /**
     * Load HTML content for query
     *
     * @param mixed $content HTML content
     * @param string $encoding Content encoding
     * @return static
     */
    public static function queryHtml(mixed $content, string $encoding = 'UTF-8'): Crawler
    {
        return static::query($content, [
            'encoding' => $encoding,
            'xml_mode' => false
        ]);
    }

    /**
     * Load XML content for query
     *
     * @param mixed $content XML content
     * @param string $encoding Content encoding
     * @param int $libxmlOptions LibXMl options
     * @return static
     */
    public static function queryXml(mixed $content, string $encoding = 'UTF-8', int $libxmlOptions = LIBXML_NONET): Crawler
    {
        return static::query($content, [
            'encoding' => $encoding,
            'xml_mode' => true,
            'libxml_options' => $libxmlOptions
        ]);
    }

    /**
     * Returns the first node of the list as XML.
     *
     * @param string|null $default When not null: the value to return when the current node is empty
     * @param int|null $options Additional Options. Currently, only LIBXML_NOEMPTYTAG is supported.
     *
     * @return string
     *
     * @throws InvalidArgumentException When current node is empty
     */
    public function xml(string $default = null, int $options = null): string
    {
        if ($this->count() < 1) {
            if (null !== $default) {
                return $default;
            }

            throw new InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);
        $owner = $node->ownerDocument;

        $xml = '';
        foreach ($node->childNodes as $child) {
            $xml .= $owner->saveXML($child, $options);
        }

        return $xml;
    }

    /**
     * Returns the first node of the list as outer XML.
     *
     * @param int|null $options Additional Options. Currently, only LIBXML_NOEMPTYTAG is supported.
     *
     * @return string
     */
    public function outerXml(int $options = null): string
    {
        if (!count($this)) {
            throw new InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);
        $owner = $node->ownerDocument;

        return $owner->saveXML($node, $options);
    }

    /**
     * Returns an array of the inner text that is the direct descendent of the current node, excluding any child nodes.
     *
     * Unlike {@see static::innerText() innerText()}, it only returns the first text node.
     * Please refer to the {@link https://symfony.com/doc/current/components/dom_crawler.html#accessing-node-values documentation} to see the differences.
     *
     * @param bool $normalizeWhitespace Whether whitespaces should be trimmed and normalized to single spaces
     * @return array
     */
    public function innerTexts(bool $normalizeWhitespace = true): array
    {
        $texts = [];

        foreach ($this->getNode(0)->childNodes as $childNode) {
            if (\XML_TEXT_NODE !== $childNode->nodeType && \XML_CDATA_SECTION_NODE !== $childNode->nodeType) {
                continue;
            }
            if (!$normalizeWhitespace) {
                $texts[] = $childNode->nodeValue;
            } else if ('' !== trim($childNode->nodeValue)) {
                $texts[] = trim(preg_replace(
                    "/(?:[ \n\r\t\x0C]{2,}+|[\n\r\t\x0C])/",
                    ' ',
                    $childNode->nodeValue
                ), " \n\r\t\x0C");
            }
        }

        return $texts;
    }
}