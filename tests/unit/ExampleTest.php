<?php

/**
 * Class ExampleTest
 * @property \citysites\yrl\YRL $parser
 */
class ExampleTest extends \Codeception\TestCase\Test
{
    public $parser;

    public function _before()
    {
        $this->parser = new \citysites\yrl\YRL();
        $name = 'realty.yrl';
        $this->parser->parse(__DIR__ . DIRECTORY_SEPARATOR . $name);
    }

    public function _after()
    {
        $this->parser = null;
    }

    // tests

    public function testOffersCount()
    {
        $this->assertEquals(7, $this->parser->getOffersCount());
    }

    public function testGenerationDate()
    {
        $this->assertEquals('2010-12-11T12:00:00+04:00', $this->parser->getDate());
    }

    public function testParseYRL()
    {
        /**
         * @var Generator $offers
         */
        $offers = $this->parser->getOffers();
        $offer = $offers->current();
        $this->assertInstanceOf(\citysites\yrl\offer\FlatOffer::class, $offer);
        $offers->next();
        $offer = $offers->current();
        $this->assertInstanceOf(\citysites\yrl\offer\FlatOffer::class, $offer);
        $offers->next();
        $offer = $offers->current();
        $this->assertInstanceOf(\citysites\yrl\offer\RoomOffer::class, $offer);
        $offers->next();
        $offer = $offers->current();
        $this->assertInstanceOf(\citysites\yrl\offer\BaseOffer::class, $offer);
        $offers->next();
        $offer = $offers->current();
        $this->assertInstanceOf(\citysites\yrl\offer\HouseOffer::class, $offer);
        $offers->next();
        $offer = $offers->current();
        $this->assertInstanceOf(\citysites\yrl\offer\CommercialOffer::class, $offer);
        $offers->next();
        $offer = $offers->current();
        $this->assertInstanceOf(\citysites\yrl\offer\GarageOffer::class, $offer);
        $offers->next();
        $offer = $offers->current();
        $this->assertEquals(null, $offer);
    }
}
