<?php

namespace Tests\Unit;

use App\Phone;
use Tests\TestCase;

class PhoneTest extends TestCase
{

    public function testValidPhoneNumberShouldBeAccepted()
    {
        // GIVEN
        $phoneId = 'foo';
        $phoneNumber = '27831234567';

        // WHEN
        $phone = Phone::from($phoneId, $phoneNumber);

        // THEN
        $this->assertEquals(Phone::VALID, $phone->getAttribute('status'));
        $this->assertEmpty($phone->getAttribute('motive'));

        // AND
        $this->assertEquals($phoneId, $phone->getAttribute('phone_id'));
        $this->assertEquals('27831234567', $phone->getAttribute('number'));
    }

    public function testValidPhoneNumberWithMissingPrefixShouldBeFixed()
    {
        // GIVEN
        $phoneId = 'foo';
        $phoneNumber = '831234567';

        // WHEN
        $phone = Phone::from($phoneId, $phoneNumber);

        // THEN
        $this->assertEquals(Phone::FIXED, $phone->getAttribute('status'));
        $this->assertEquals(Phone::MOTIVE_MISSING_PREFIX, $phone->getAttribute('motive'));

        // AND
        $this->assertEquals($phoneId, $phone->getAttribute('phone_id'));
        $this->assertEquals('27831234567', $phone->getAttribute('number'));
    }

    public function testNullPhoneNumberShouldBeRejected()
    {
        // GIVEN
        $phoneId = 'foo';
        $phoneNumber = null;

        // WHEN
        $phone = Phone::from($phoneId, $phoneNumber);

        // THEN
        $this->assertEquals(Phone::REJECTED, $phone->getAttribute('status'));
        $this->assertEquals(Phone::MOTIVE_NOT_A_NUMBER, $phone->getAttribute('motive'));

        // AND
        $this->assertEquals($phoneId, $phone->getAttribute('phone_id'));
        $this->assertEquals($phoneNumber, $phone->getAttribute('number'));
    }

    public function testInvalidPhoneNumberShouldBeRejected()
    {
        // GIVEN
        $phoneId = 'foo';
        $phoneNumber = 'invalidPhoneNumber';

        // WHEN
        $phone = Phone::from($phoneId, $phoneNumber);

        // THEN
        $this->assertEquals(Phone::REJECTED, $phone->getAttribute('status'));
        $this->assertEquals(Phone::MOTIVE_NOT_A_NUMBER, $phone->getAttribute('motive'));

        // AND
        $this->assertEquals($phoneId, $phone->getAttribute('phone_id'));
        $this->assertEquals($phoneNumber, $phone->getAttribute('number'));
    }

    public function testPhoneNumberWithInvalidRegionShouldBeRejected()
    {
        // GIVEN
        $phoneId = 'foo';
        $phoneNumber = '34608918253';

        // WHEN
        $phone = Phone::from($phoneId, $phoneNumber);

        // THEN
        $this->assertEquals(Phone::REJECTED, $phone->getAttribute('status'));
        $this->assertEquals(Phone::MOTIVE_INVALID_NUMBER_FOR_REGION, $phone->getAttribute('motive'));

        // AND
        $this->assertEquals($phoneId, $phone->getAttribute('phone_id'));
        $this->assertEquals($phoneNumber, $phone->getAttribute('number'));
    }

    public function testDeletedAndValidPhoneNumberShouldBeRejected()
    {
        // GIVEN
        $phoneId = 'foo';
        $phoneNumber = 'DELETED_27831234567';

        // WHEN
        $phone = Phone::from($phoneId, $phoneNumber);

        // THEN
        $this->assertEquals(Phone::REJECTED, $phone->getAttribute('status'));
        $this->assertEquals(Phone::MOTIVE_DELETED, $phone->getAttribute('motive'));

        // AND
        $this->assertEquals($phoneId, $phone->getAttribute('phone_id'));
        $this->assertEquals($phoneNumber, $phone->getAttribute('number'));
    }
}
