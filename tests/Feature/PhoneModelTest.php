<?php

namespace Tests\Feature;

use App\Phone;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneModelTest extends TestCase
{
    use RefreshDatabase;

    public function testValidEntryShouldBeInserted()
    {
        // GIVEN
        $phone = Phone::create([
            'phone_id' => 'id',
            'number' => '123',
            'status' => 'valid'
        ]);

        // WHEN
        $phone->save();

        // THEN
        $this->assertDatabaseHas('phones', ['number' => '123']);
    }

    public function testMissingMandatoryFieldShouldThrowEx()
    {
        // GIVEN testShouldReject
        $this->expectException(QueryException::class);

        // AND
        $phone = Phone::create(['phone_id' => 'id']);

        // WHEN
        $phone->save();
    }

}
