<?php

namespace citysites\yrl;

use citysites\yrl\offer\BaseOffer;
use citysites\yrl\offer\CommercialOffer;
use citysites\yrl\offer\FlatOffer;
use citysites\yrl\offer\GarageOffer;
use citysites\yrl\offer\HouseOffer;
use citysites\yrl\offer\LotOffer;
use citysites\yrl\offer\RoomOffer;

/**
 * Class YRL
 * @package citysites\yrl
 */
class YRL {

    /**
     * @var \XMLReader
     */
    protected $XMLReader;

    protected $uri;

    protected $schema;

    protected $date;

    protected $offersCount;

    protected $errors = [];

    protected $pathArr = [];

    protected $path;

    public function __construct()
    {
        $this->XMLReader = new \XMLReader();
    }

    /**
     * @return \Generator
     */
    public function getOffers()
    {
        $this->open();
        while ($this->read()) {
            if ('realty-feed/offer' == $this->path) {
                try {
                    yield $this->parseOffer();
                } catch (\Exception $e) {
                    $this->errors[] = $e->getMessage();
                    continue;
                }
            }
        }
        $this->close();
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return integer
     */
    public function getOffersCount()
    {
        return $this->offersCount;
    }

    /**
     * @param string $uri
     * @param string|boolean $schema
     * @throws \Exception
     */
    public function parse($uri, $schema = null)
    {
        $this->uri = $uri;
        if (is_string($schema)) {
            $this->schema = $schema;
        } elseif (false !== $schema) {
            $this->schema = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'yrl_schema.xsd';
        }
        $this->open();
        while ($this->read()) {
            if ('realty-feed/generation-date' == $this->path) {
                $this->date = $this->XMLReader->readString();
            }
            if ('realty-feed/offer' == $this->path) {
                $this->offersCount = $this->parseOffersCount();
                break;
            }
        }
        $this->close();
    }

    /**
     * @throws \Exception
     */
    protected function open()
    {
        $uri = (string)$this->uri;
        if (!$this->XMLReader->open($uri)) {
            throw new \Exception("Failed to open XML file '{$uri}'");
        }

        if (!empty($this->schema)) {
            $schema = (string)$this->schema;
            if (!$this->XMLReader->setSchema($schema)) {
                throw new \Exception("Failed to open XML Schema file '{$schema}'");
            }
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function read()
    {
        $xml  = $this->XMLReader;
        if ($xml->read()) {
            if ($xml->nodeType == \XMLReader::ELEMENT && !$xml->isEmptyElement) {
                array_push($this->pathArr, $xml->name);
                $this->path = implode('/', $this->pathArr);
            } elseif ($xml->nodeType == \XMLReader::END_ELEMENT) {
                array_pop($this->pathArr);
                $this->path = implode('/', $this->pathArr);
            }
            return true;
        }
        return false;
    }

    protected function close()
    {
        $this->path = '';
        $this->pathArr = [];
        $this->XMLReader->close();
    }

    protected function parseOffersCount()
    {
        $count = 1;
        $xml = $this->XMLReader;

        while ($xml->next($xml->localName)) {
            $count++;
        }
        return $count;
    }

    protected function parseOffer()
    {
        $offerNode = $this->parseNode('realty-feed');
        $category = array_values(array_filter($offerNode['nodes'], function($item) {
            return 'category' == $item['name'];
        }));
        try {
            $offer = $this->createOffer($category[0]['value'] ?? '')->setOptions($offerNode);
        } catch (\InvalidArgumentException $ex) {
            if (isset($offerNode['attributes']['internal-id'])) {
                throw new \InvalidArgumentException(
                    sprintf('Offer internal-id = %s, error: %s',
                        $offerNode['attributes']['internal-id'],
                        $ex->getMessage()));
            }
        }
        return $offer;
    }

    protected function parseNode($basePath)
    {
        $xml = $this->XMLReader;
        $name = $xml->name;
        $path = $basePath . '/' . $name;
        $value = '';
        $nodes = [];

        $attributes = $this->parseAttributes();

        if (!$xml->isEmptyElement) {
            while ($this->read()) {
                if ($xml->nodeType == \XMLReader::ELEMENT) {
                    $nodes[] = $this->parseNode($path);
                } elseif (($xml->nodeType == \XMLReader::TEXT || $xml->nodeType == \XMLReader::CDATA) && $xml->hasValue) {
                    $value .= $xml->value;
                } elseif ($this->path == $basePath) {
                    break;
                }
            }
        }

        return ['name' => $name, 'attributes' => $attributes, 'value' => trim($value), 'nodes' => $nodes];
    }

    /**
     * @return array
     */
    protected function parseAttributes()
    {
        $xml = $this->XMLReader;
        $attributes = [];
        if ($xml->hasAttributes) {
            while ($xml->moveToNextAttribute()) {
                $attributes[$xml->name] = $xml->value;
            }
        }
        return $attributes;
    }

    /**
     * @param string $type
     * @return BaseOffer|CommercialOffer|FlatOffer|HouseOffer|LotOffer|RoomOffer|GarageOffer
     * @throws \Exception
     */
    protected function createOffer($type)
    {
        $type = mb_strtolower($type);
        switch ($type) {
            case 'дом' :
            case 'house' :
            case 'часть дома' :
            case 'дом с участком' :
            case 'house with lot' :
                return new HouseOffer();
            case 'квартира' :
            case 'flat' :
                return new FlatOffer();
            case 'коммерческая' :
            case 'commercial' :
            case 'office' :
            case 'офисное помещение' :
            case 'готовый бизнес' :
            case 'business' :
            case 'помещения свободного назначения' :
            case 'free purpose' :
            case 'автосервис' :
            case 'auto repair' :
            case 'гостиница' :
            case 'hotel' :
            case 'land' :
            case 'земли коммерческого назначения' :
            case 'юридический адрес' :
            case 'legal address' :
            case 'manufacturing' :
            case 'производственное помещение' :
            case 'public catering' :
            case 'общепит' :
            case 'retail' :
            case 'торговые помещения' :
            case 'warehouse' :
            case 'склад' :
                return new CommercialOffer();
            case 'комната' :
            case 'room' :
                return new RoomOffer();
            case 'участок' :
            case 'lot' :
                return new LotOffer();
            case 'гараж' :
            case 'garage' :
                return new GarageOffer();
            case 'дача' :
            case 'коттедж' :
            case 'cottage' :
            case 'таунхаус' :
            case 'townhouse' :
                return new BaseOffer();
            default :
                throw new \InvalidArgumentException('(category) Неизвестная категория объекта - "'. $type. '"!');
                break;
        }
    }
}
