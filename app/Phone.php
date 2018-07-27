<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class Phone extends Model
{
    const REGION = 'ZA';

    const VALID = 'valid';
    const FIXED = 'fixed';
    const REJECTED = 'rejected';

    const MOTIVE_MISSING_PREFIX = 'missing_prefix';
    const MOTIVE_DELETED = 'deleted';
    const MOTIVE_NOT_A_NUMBER = 'not_a_number';
    const MOTIVE_INVALID_NUMBER_FOR_REGION = 'invalid_number_for_region';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone_id', 'number', 'status', 'motive'
    ];


    private static function isDeleted(string $phoneNumber): bool
    {
        return Str::contains($phoneNumber, 'DELETED');
    }

    /**
     * @param string $phoneId
     * @param string $phoneNumber
     * @return Phone
     */
    public static function from(string $phoneId = null, string $phoneNumber = null): Phone
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        $id = isset($phoneId) ? trim($phoneId) : '';
        $number = isset($phoneNumber) ? trim($phoneNumber) : '';

        if (Phone::isDeleted($number)) {
            return new Phone(['phone_id' => $id, 'number' => $number, 'status' => Phone::REJECTED, 'motive' => Phone::MOTIVE_DELETED]);
        }

        try {
            $phoneNumberProto = $phoneUtil->parse($number, Phone::REGION);
        } catch (NumberParseException $e) {
            Log::error('Exception while processing phone', [
                'number' => $number,
                'message' => $e->getMessage()]);

            return new Phone([
                    'phone_id' => $id,
                    'number' => $number,
                    'status' => Phone::REJECTED,
                    'motive' => Phone::MOTIVE_NOT_A_NUMBER]
            );
        }

        if (!$phoneUtil->isValidNumber($phoneNumberProto)) {
            Log::debug('Invalid phone number processed', ['number' => $number]);

            return new Phone([
                'phone_id' => $id,
                'number' => $number,
                'status' => Phone::REJECTED,
                'motive' => Phone::MOTIVE_INVALID_NUMBER_FOR_REGION
            ]);
        }

        $nationalNumber = $phoneNumberProto->getNationalNumber();
        $countryCode = $phoneNumberProto->getCountryCode();

        $normalizedNumber = $countryCode . $nationalNumber;
        Log::debug("$normalizedNumber, $number");
        $fixed = $normalizedNumber !== $number;

        Log::debug('Valid phone number processed', [
            'fixed' => $fixed,
            'countryCode' => $countryCode,
            'nationalNumber' => $nationalNumber,
            'normalizedNumber' => $countryCode . $nationalNumber]);


        return new Phone([
            'phone_id' => $id,
            'number' => $normalizedNumber,
            'status' => $fixed ? Phone::FIXED : Phone::VALID,
            'motive' => $fixed ? Phone::MOTIVE_MISSING_PREFIX : null
        ]);
    }
}
