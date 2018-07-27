<?php

namespace App\Http\Controllers;

use App\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PhoneController extends Controller
{
    public function home()
    {
        return view('submitPhone', ['number' => '']);
    }

    public function submitPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|min:8|max:255',
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput($request->all())
                ->withErrors($validator);
        }

        $phone = Phone::from(null, $request->get('number'));

        return back()
            ->with('number', $phone->getAttribute('number'))
            ->with('status', $phone->getAttribute('status'))
            ->with('motive', $phone->getAttribute('motive'));
    }
}
