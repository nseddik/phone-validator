<?php

namespace Tests\Feature;

use App\Phone;
use Illuminate\Http\Response;
use Tests\TestCase;

class PhoneControllerTest extends TestCase
{
    public function testShouldDisplaySubmitPhoneForm()
    {

        //GIVEN

        // WHEN
        $response = $this->get('/phone');


        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('submitPhone');
        $response->assertViewHas('number', '');
    }

    public function testSubmitFormWithMissingNumberShouldBeRejected()
    {

        //GIVEN
        $this->withoutMiddleware('App\Http\Middleware\VerifyCsrfToken');

        // WHEN
        $response = $this->post('/phone', ['number' => '']);


        // THEN
        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertSessionHas('number', '');
        $response->assertSessionHasErrors('number', 'The number field is required.');
    }

    public function testSubmitFormWithInvalidNumberShouldBeTested()
    {
        //GIVEN
        $this->withoutMiddleware('App\Http\Middleware\VerifyCsrfToken');

        // WHEN
        $response = $this->post('/phone', ['number' => 'not_a_phone_number']);


        // THEN
        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertSessionHas('number', 'not_a_phone_number');

        // AND
        $response->assertSessionHas('status', Phone::REJECTED);
        $response->assertSessionHas('motive', Phone::MOTIVE_NOT_A_NUMBER);
    }
}
