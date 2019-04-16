<?php

use Egulias\EmailValidator\EmailValidator;

class Swift_Mime_Headers_PathHeaderTest extends \PHPUnit\Framework\TestCase
{
    public function testTypeIsPathHeader()
    {
        $header = $this->getHeader('Return-Path');
        $this->assertEquals(Swift_Mime_Header::TYPE_PATH, $header->getFieldType());
    }

    public function testSingleAddressCanBeSetAndFetched()
    {
        $header = $this->getHeader('Return-Path');
        $header->setAddress('chris@vendor.org');
        $this->assertEquals('chris@vendor.org', $header->getAddress());
    }

    /**
     * @expectedException \Exception
     */
    public function testAddressMustComplyWithRfc2822()
    {
        $header = $this->getHeader('Return-Path');
        $header->setAddress('chr is@vendor.org');
    }

    public function testValueIsAngleAddrWithValidAddress()
    {
        /* -- RFC 2822, 3.6.7.

            return          =       "Return-Path:" path CRLF

            path            =       ([CFWS] "<" ([CFWS] / addr-spec) ">" [CFWS]) /
                                                            obs-path
     */

        $header = $this->getHeader('Return-Path');
        $header->setAddress('chris@vendor.org');
        $this->assertEquals('<chris@vendor.org>', $header->getFieldBody());
    }

    public function testAddressIsIdnEncoded()
    {
        $header = $this->getHeader('Return-Path');
        $header->setAddress('chris@swïftmailer.org');
        $this->assertEquals('<chris@xn--swftmailer-78a.org>', $header->getFieldBody());
    }

    /**
     * @expectedException \Swift_AddressEncoderException
     */
    public function testAddressMustBeEncodable()
    {
        $header = $this->getHeader('Return-Path');
        $header->setAddress('chrïs@vendor.org');
        $header->getFieldBody();
    }

    public function testValueIsEmptyAngleBracketsIfEmptyAddressSet()
    {
        $header = $this->getHeader('Return-Path');
        $header->setAddress('');
        $this->assertEquals('<>', $header->getFieldBody());
    }

    public function testSetBodyModel()
    {
        $header = $this->getHeader('Return-Path');
        $header->setFieldBodyModel('foo@bar.tld');
        $this->assertEquals('foo@bar.tld', $header->getAddress());
    }

    public function testGetBodyModel()
    {
        $header = $this->getHeader('Return-Path');
        $header->setAddress('foo@bar.tld');
        $this->assertEquals('foo@bar.tld', $header->getFieldBodyModel());
    }

    public function testToString()
    {
        $header = $this->getHeader('Return-Path');
        $header->setAddress('chris@vendor.org');
        $this->assertEquals('Return-Path: <chris@vendor.org>'."\r\n",
            $header->toString()
            );
    }

    private function getHeader($name)
    {
        return new Swift_Mime_Headers_PathHeader($name, new EmailValidator(), new Swift_AddressEncoder_IdnAddressEncoder());
    }
}
