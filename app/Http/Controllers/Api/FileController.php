<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\Files;

class FileController extends Controller
{
    /**
     * Upload file and save file name.
     */
    public function upload(Request $request) {
        $request->validate([
            'files' => 'required',
            'files.*' => 'required|mimes:pdf,csv,xls,xlsx,doc,docx|max:2048',
        ]);

        $files = [];

        if ($request->hasfile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = time().rand(1,100).'.'.$file->extension();
                $fileOriginalName = $file->getClientOriginalName();

                if ($file->move(public_path('uploads'), $fileName)) {
                    $files[] = $fileName;
                    Files::create([
                        "file_name" => $fileName,
                        'file_original_name' => $fileOriginalName,
                    ]);
                }
            }
        }
        if (count($files) > 0) {
            return back()->with('success','Success! File(s) has been uploaded');
        }

        else {
            return back()->with("failed", "Alert! Unable to upload file");
        }
    }
}
