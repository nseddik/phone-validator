<?php

namespace App\Http\Controllers;

use App\Phone;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use SplFileObject;

class PhoneApiController extends Controller
{
    private static function stripNonAsciiChars($val)
    {
        return preg_replace('/[[:^print:]]/', "", $val);
    }

    public function uploadedReport(Request $request)
    {
        $status = $request->input('status');

        if (in_array($status, [Phone::VALID, Phone::FIXED, Phone::REJECTED])) {
            return response()->json(Phone::all(['number', 'phone_id', 'status', 'motive'])->where('status', '=', $status));
        }

        return response()->json(Phone::all(['number', 'phone_id', 'status', 'motive']));

    }

    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phoneFile' => [
                'required', 'file', 'min:1', 'max:1024000', function ($attribute, UploadedFile $value, $fail) {
                    $headers = $value->openFile('r')->fgetcsv();
                    $sanitized = array_map('self::stripNonAsciiChars', $headers);
                    $sanitized = array_map('trim', $sanitized);

                    if ($sanitized != ['id', 'sms_phone']) {
                        $fail("$attribute expected header [id,sms_phone] not found.");
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        }

        $phoneFile = $request->file('phoneFile');
        $handle = $phoneFile->openFile('r');

        // skip empty lines
        $handle->setFlags(SplFileObject::SKIP_EMPTY);

        // skip first header line
        $handle->fgetcsv();

        while (!$handle->eof()) {
            $line = $handle->fgetcsv();

            if (count($line) != 2) {
                // TODO: fill error map
                continue;
            }

            // sanitize wrongly encoded entries
            $sanitized = array_map('self::stripNonAsciiChars', $line);

            Phone::from($sanitized[0], $sanitized[1])->save();
        }

        return response()->json([], Response::HTTP_ACCEPTED);
    }
}
