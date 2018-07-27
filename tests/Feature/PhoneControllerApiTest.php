<?php

namespace Tests\Feature;

use App\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PhoneControllerApiTest extends TestCase
{
    use RefreshDatabase;

    public function testMissingFileShouldReturnA400()
    {
        // GIVEN

        // WHEN
        $response = $this->json('POST', '/api/phone');

        // THEN
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson(['phoneFile' => ['The phone file field is required.']]);
    }

    public function testEmptyFileShouldReturnA400()
    {
        // GIVEN
        $emptyPhoneFile = UploadedFile::fake()->create('any.csv');

        // WHEN
        $response = $this->json('POST', '/api/phone', [
            'phoneFile' => $emptyPhoneFile
        ]);

        // THEN
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['phoneFile' => ['The phone file must be at least 1 kilobytes.']]);
    }


    public function testFileTooBigShouldReturnA400()
    {
        // GIVEN
        $bigPhoneFile = UploadedFile::fake()->create('any.csv', 1024 * 1000 + 1);

        // WHEN
        $response = $this->json('POST', '/api/phone', [
            'phoneFile' => $bigPhoneFile
        ]);

        // THEN
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['phoneFile' => ['The phone file may not be greater than 1024000 kilobytes.']]);
    }

    public function testFileWithInvalidOrMissingHeaderShouldFail()
    {
        // GIVEN
        $phoneFile = UploadedFile::fake()->create('any.csv', 10);
        $fileObject = $phoneFile->openFile('w+');

        // AND
        $fileObject->fputcsv(['wrong', 'header']);

        // WHEN
        $response = $this->json('POST', '/api/phone', [
            'phoneFile' => $phoneFile
        ]);

        // THEN
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['phoneFile' => ['phoneFile expected header [id,sms_phone] not found.']]);
    }

    public function testFileWithOnlyHeaderShouldBeAccepted()
    {
        // GIVEN
        $phoneFile = UploadedFile::fake()->create('any.csv', 10);
        $fileObject = $phoneFile->openFile('w+');

        // AND
        $fileObject->fputcsv(['id', 'sms_phone']);

        // WHEN
        $response = $this->json('POST', '/api/phone', [
            'phoneFile' => $phoneFile
        ]);

        // THEN
        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertExactJson([]);
    }

    public function testFileWithHeaderAndLinesShouldBeAcceptedAndContentShouldBeStored()
    {
        // GIVEN
        $phoneFile = UploadedFile::fake()->create('any.csv', 10);
        $fileObject = $phoneFile->openFile('w+');

        // AND
        $fileObject->fputcsv(['id', 'sms_phone']);
        $fileObject->fputcsv(['1', '6478342944']);
        $fileObject->fputcsv(['2', 'invalid']);
        $fileObject->fputcsv(['3', '27831234567']);
        $fileObject->fputcsv(['4', '+27831234568']);
        $fileObject->fputcsv(['5', 'DELETED_27831234567']);

        // WHEN
        $response = $this->json('POST', '/api/phone', [
            'phoneFile' => $phoneFile
        ]);

        // THEN
        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertExactJson([]);

        // AND
        $this->assertDatabaseHas('phones', ['number' => '6478342944', 'status' => Phone::REJECTED, 'motive' => Phone::MOTIVE_INVALID_NUMBER_FOR_REGION]);
        $this->assertDatabaseHas('phones', ['number' => 'invalid', 'status' => Phone::REJECTED, 'motive' => Phone::MOTIVE_NOT_A_NUMBER]);
        $this->assertDatabaseHas('phones', ['number' => '27831234567', 'status' => Phone::VALID, 'motive' => null]);
        $this->assertDatabaseHas('phones', ['number' => '27831234568', 'status' => Phone::FIXED, 'motive' => Phone::MOTIVE_MISSING_PREFIX]);
        $this->assertDatabaseHas('phones', ['number' => 'DELETED_27831234567', 'status' => Phone::REJECTED, 'motive' => Phone::MOTIVE_DELETED]);
    }

    public function testShouldDisplayAllStoredPhones()
    {
        // GIVEN
        Phone::insert(
            array(
                array(
                    'phone_id' => '1',
                    'number' => '1',
                    'status' => Phone::VALID,
                    'motive' => null
                ),
                array(
                    'phone_id' => '2',
                    'number' => '2',
                    'status' => Phone::REJECTED,
                    'motive' => 'motive'
                )
            ));

        // WHEN
        $response = $this->json('GET', '/api/phone');

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response->assertExactJson([
            [
                'phone_id' => '1',
                'number' => '1',
                'status' => Phone::VALID,
                'motive' => null
            ],
            [
                'phone_id' => '2',
                'number' => '2',
                'status' => Phone::REJECTED,
                'motive' => 'motive'
            ]]);
    }

    public function testShouldDisplayAllStoredPhonesWithStatus()
    {
        // GIVEN
        Phone::insert(
            array(
                array(
                    'phone_id' => '1',
                    'number' => '1',
                    'status' => Phone::VALID,
                    'motive' => null
                ),
                array(
                    'phone_id' => '2',
                    'number' => '2',
                    'status' => Phone::REJECTED,
                    'motive' => 'motive'
                )
            ));

        // WHEN
        $response = $this->call('GET', '/api/phone', ['status' => Phone::VALID]);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response->assertExactJson([
            [
                'phone_id' => '1',
                'number' => '1',
                'status' => Phone::VALID,
                'motive' => null
            ]]);
    }

}
