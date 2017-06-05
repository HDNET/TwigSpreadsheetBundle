<?php

namespace MewesK\TwigSpreadsheetBundle\Wrapper;

use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooter;

/**
 * Class XlsHeaderFooterWrapper
 *
 * @package MewesK\TwigSpreadsheetBundle\Wrapper
 */
class XlsHeaderFooterWrapper extends AbstractWrapper
{
    /**
     * @var array
     */
    protected $context;
    /**
     * @var \Twig_Environment
     */
    protected $environment;
    /**
     * @var XlsSheetWrapper
     */
    protected $sheetWrapper;

    /**
     * @var null|HeaderFooter
     */
    protected $object;
    /**
     * @var array
     */
    protected $attributes;
    /**
     * @var array
     */
    protected $mappings;
    /**
     * @var array
     */
    protected $alignmentAttributes;

    /**
     * XlsHeaderFooterWrapper constructor.
     *
     * @param array $context
     * @param \Twig_Environment $environment
     * @param XlsSheetWrapper $sheetWrapper
     */
    public function __construct(array $context, \Twig_Environment $environment, XlsSheetWrapper $sheetWrapper)
    {
        $this->context = $context;
        $this->environment = $environment;
        $this->sheetWrapper = $sheetWrapper;

        $this->object = null;
        $this->attributes = [];
        $this->mappings = [];
        $this->alignmentAttributes = [];

        $this->initializeMappings();
    }

    protected function initializeMappings()
    {
        $this->mappings['scaleWithDocument'] = function ($value) {
            $this->object->setScaleWithDocument($value);
        };
        $this->mappings['alignWithMargins'] = function ($value) {
            $this->object->setAlignWithMargins($value);
        };
    }

    /**
     * @param string $type
     * @param array $properties
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function start(string $type, array $properties = [])
    {
        if ($this->sheetWrapper->getObject() === null) {
            throw new \LogicException();
        }
        if (in_array(strtolower($type),
                ['header', 'oddheader', 'evenheader', 'firstheader', 'footer', 'oddfooter', 'evenfooter', 'firstfooter'],
                true) === false
        ) {
            throw new \InvalidArgumentException(sprintf('Unknown type "%s"', $type));
        }

        $this->object = $this->sheetWrapper->getObject()->getHeaderFooter();
        $this->attributes['value'] = ['left' => null, 'center' => null, 'right' => null]; // will be generated by the alignment tags
        $this->attributes['type'] = $type;
        $this->attributes['properties'] = $properties;

        $this->setProperties($properties, $this->mappings);
    }

    public function end()
    {
        $value = implode('', $this->attributes['value']);

        switch (strtolower($this->attributes['type'])) {
            case 'header':
                $this->object->setOddHeader($value);
                $this->object->setEvenHeader($value);
                $this->object->setFirstHeader($value);
                break;
            case 'oddheader':
                $this->object->setDifferentOddEven(true);
                $this->object->setOddHeader($value);
                break;
            case 'evenheader':
                $this->object->setDifferentOddEven(true);
                $this->object->setEvenHeader($value);
                break;
            case 'firstheader':
                $this->object->setDifferentFirst(true);
                $this->object->setFirstHeader($value);
                break;
            case 'footer':
                $this->object->setOddFooter($value);
                $this->object->setEvenFooter($value);
                $this->object->setFirstFooter($value);
                break;
            case 'oddfooter':
                $this->object->setDifferentOddEven(true);
                $this->object->setOddFooter($value);
                break;
            case 'evenfooter':
                $this->object->setDifferentOddEven(true);
                $this->object->setEvenFooter($value);
                break;
            case 'firstfooter':
                $this->object->setDifferentFirst(true);
                $this->object->setFirstFooter($value);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown type "%s"', $this->attributes['type']));
        }

        $this->object = null;
        $this->attributes = [];
    }

    /**
     * @param string $type
     * @param array $properties
     * @throws \InvalidArgumentException
     */
    public function startAlignment(string $type, array $properties = [])
    {
        $this->alignmentAttributes['type'] = $type;
        $this->alignmentAttributes['properties'] = $properties;

        switch (strtolower($type)) {
            case 'left':
                $this->attributes['value']['left'] = '&L';
                break;
            case 'center':
                $this->attributes['value']['center'] = '&C';
                break;
            case 'right':
                $this->attributes['value']['right'] = '&R';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown alignment type "%s"', $this->alignmentAttributes['type']));
        }
    }

    /**
     * @param string $value
     * @throws \InvalidArgumentException
     */
    public function endAlignment($value)
    {
        switch (strtolower($this->alignmentAttributes['type'])) {
            case 'left':
                if (strpos($this->attributes['value']['left'], '&G') === false) {
                    $this->attributes['value']['left'] .= $value;
                }
                break;
            case 'center':
                if (strpos($this->attributes['value']['center'], '&G') === false) {
                    $this->attributes['value']['center'] .= $value;
                }
                break;
            case 'right':
                if (strpos($this->attributes['value']['right'], '&G') === false) {
                    $this->attributes['value']['right'] .= $value;
                }
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown alignment type "%s"', $this->alignmentAttributes['type']));
        }

        $this->alignmentAttributes = [];
    }

    //
    // Getters/Setters
    //

    /**
     * @return null|HeaderFooter
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param null|HeaderFooter $object
     */
    public function setObject(HeaderFooter $object = null)
    {
        $this->object = $object;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }

    /**
     * @param array $mappings
     */
    public function setMappings(array $mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * @return array
     */
    public function getAlignmentAttributes(): array
    {
        return $this->alignmentAttributes;
    }

    /**
     * @param array $alignmentAttributes
     */
    public function setAlignmentAttributes(array $alignmentAttributes)
    {
        $this->alignmentAttributes = $alignmentAttributes;
    }
}
