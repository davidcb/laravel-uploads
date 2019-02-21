<?php

namespace Davidcb\Uploads\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Pagination;
use Davidcb\Uploads\Http\Controllers\ImageController;
use Davidcb\Uploads\Models\File;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Uploads a file to storage
     * @return \Illuminate\Http\Response
     */
    public function upload()
    {
        if (request()->hasFile('file') && request()->file('file')->isValid()) {
            $uploadedFile = request()->file('file');

            $extension = $uploadedFile->getClientOriginalExtension();

            $filename = str_random(20) . '.' . $extension;

            Storage::put(
                request()->folder . '/' . $filename,
                file_get_contents($uploadedFile->getRealPath())
            );

            if (request()->cropType) {
                $imageController = new ImageController;
                $imageController->crop(request()->folder, $filename, request()->cropType);
            }

            $response = [
                'message' => 'Successfuly uploaded',
                'folder' => request()->folder,
                'filename' => $filename,
                'cropType' => request()->cropType
            ];

            return response()->json($response, 200);
        }
        
        return response()->json('Error uploading file', 401);
    }

    /**
     * Deletes a file from storage given its folder and URL
     * @param  string $folder
     * @param  string $url
     * @return \Illuminate\Http\Response
     */
    public function deleteStorage($folder, $url)
    {
        

        if ($folder && $url) {
            $paths = [
                $folder . '/' . $url
            ];

            $directories = Storage::directories($folder . '/');

            if (sizeof($directories)) {
                foreach ($directories as $directory) {
                    $paths[] = $directory . '/' . $url;
                }
            }

            foreach ($paths as $path) {
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
            }
        }

        return response()->json('Successfully deleted', 200);
    }

    /**
     * Deletes a File model given its folder and URL.
     * @param  string $folder
     * @param  string $url
     * @return \Illuminate\Http\Response
     */
    public function deleteModel(File $file)
    {
        if ($file) {
            $this->deleteStorage($file->folder, $file->url);
            $file->delete();
            return response()->json('Successfully deleted', 200);
        }

        return response()->json('File not found', 404);
    }

    /**
     * Downloads a file from a folder with the given title.
     * @param  string $folder
     * @param  string $filename
     * @param  string $title
     * @return \Illuminate\Http\Response
     */
    public function download($folder, $filename, $title = null)
    {
        $path = $folder . '/' . $filename;
        $fs = Storage::getDriver();

        $extension = pathinfo(storage_path('app/' . $path), PATHINFO_EXTENSION);

        $headers = [
            "Content-Type" => $fs->getMimetype($path),
            "Content-Length" => $fs->getSize($path),
            "Content-disposition" => "attachment; filename=\"" . basename($path) . "\"",
        ];

        if ($title) {
            $title .= '.' . $extension;
        } else {
            $title = $filename;
        }

        return response()->download(storage_path('app/' . $path), $title, $headers);
    }

    /**
     * Sorts all files by the given positions
     * @return \Illuminate\Http\Response
     */
    public function sort()
    {
        $positions = explode(';', request()->positions);
        $files = File::get();
        for ($i = 0, $n = sizeof($positions); $i < $n; $i++) {
            foreach ($files as $file) {
                if ($file->id == $positions[$i] && $file->orderby != $i + 1) {
                    $file->orderby = $i + 1;
                    $file->save();
                }
            }
        }

        return response()->json('Successfully sorted', 200);
    }
}
