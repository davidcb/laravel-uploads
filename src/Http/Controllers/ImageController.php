<?php

namespace Davidcb\Uploads\Http\Controllers;

use App\Http\Controllers\Controller;
use Davidcb\Uploads\Models\Image as ImageModel;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageController extends Controller {

    /**
     * Shows an image with the given filters
     * @param  string $folder
     * @param  string $url
     * @param  string $filter
     * @return \Illuminate\Http\Response
     */
    public function view($folder = null, $url = null, $filter = null) {

        $request = request()->instance();

        $filePath = $folder . '/' . $url;

        $file = Storage::get($filePath);

        $extension = explode('.', $url);

        $image = Image::make($file);

        if ($filter != 'admin') {
            if (request()->cookie('resolution')) {
                if ($image->width() > request()->cookie('resolution')) {
                    $image->widen(request()->cookie('resolution'));
                }
            }
        }

        if ($filter == 'grey') {
            $image->greyscale();
        }

        if ($filter == 'watermark') {
            $watermarkImage = Image::make(public_path('images/logo-orange.png'));
            $watermarkImage->resize($watermarkImage->width() * 0.4, $watermarkImage->height() * 0.4);
            $x = 0;
            $y = round($image->height() / 10);

            while ($x < $image->width()) {
                $image->insert($watermarkImage, 'bottom-left', $x, $y);
                $image->insert($watermarkImage, 'top-left', $x, $y);
                $x += $watermarkImage->width();
            }
        }

        if ($extension[1] == 'png') {
            $file = $image->encode('png');
            $mime = 'image/png';
        } else {
            $file = $image->encode('jpg');
            $mime = 'image/jpeg';
        }

        $headers = [
            'Content-Type' => $mime
        ];

        return $this->prepareResponse($file);
    }

    /**
     * Shows an svg image
     * @param  string $folder
     * @param  string $url
     * @return \Illuminate\Http\Response
     */
    public function viewSvg($folder = null, $url = null) {

        $filePath = $folder . '/' . $url;

        $file = Storage::get($filePath);

        return (new Response($file, 200))->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Shows a placeholdere image for a given cropType
     * @param  int $cropType
     * @return \Illuminate\Http\Response
     */
    public function placeholder($cropType)
    {
        $cuts = config('crop.types')[$cropType - 1];

        $image = Image::canvas($cuts[0]['width'], $cuts[0]['height'], '#aaa');

        $watermarkSource = public_path('images/logo.png');
        $watermarkImage = Image::make($watermarkSource);

        $image->insert($watermarkImage, 'center');

        $file = $image->encode('jpg');

        $headers = [
            'Content-Type' => 'image/jpeg'
        ];

        return $this->prepareResponse($file);
    }

    /**
     * Crops an image either automatically given a cropType
     * or from the given coordinates and dimensions
     * @param  string $folder
     * @param  string $url
     * @param  int $cropType
     * @return \Illuminate\Http\Response
     */
    public function crop($folder, $url, $cropType)
    {

        ini_set('memory_limit', '256M');

        $url = urldecode($url);

        $extension = explode('.', $url);

        if (sizeof($extension) > 1 && (strtolower($extension[1]) == 'jpg' || strtolower($extension[1]) == 'jpeg' || strtolower($extension[1]) == 'png')) {

            $cuts = config('crop.types')[$cropType - 1];

            foreach ($cuts as $cut) {
                if (Storage::exists($folder . '/' . $url)) {
                    $image = Image::make(Storage::get($folder . '/' . $url));

                    if (request()->w && request()->h) {
                        $image->crop(round(request()->w), round(request()->h), round(request()->x), round(request()->y));
                        if (isset($cut['width']) && isset($cut['height'])) {
                            $image->resize($cut['width'], $cut['height'])->stream();
                        } elseif (isset($cut['width'])) {
                            $image->widen($cut['width'])->stream();
                        } elseif (isset($cut['height'])) {
                            $image->heighten($cut['height'])->stream();
                        }
                    } else {
                        if (isset($cut['width']) && isset($cut['height']) && $cut['width'] && $cut['height']) {
                            $image->fit($cut['width'], $cut['height'])->stream();
                        } elseif (isset($cut['width']) && $cut['width']) {
                            $image->widen($cut['width'])->stream();
                        } elseif (isset($cut['height']) && $cut['height']) {
                            $image->heighten($cut['height'])->stream();
                        }
                    }

                    Storage::put(
                        $folder . '/' . $cut['prefix'] . $url,
                        $image->__toString()
                    );
                }
            }

        }

        $response = [
            'message' => 'Successfuly cropped',
            'folder' => $folder,
            'filename' => $url,
            'cropType' => $cropType
        ];

        return response()->json($response, 200);

    }

    /**
     * Deletes an Image model from the database
     * @param  \Davidcb\Uploads\Models\Image $image
     * @return \Illuminate\Http\Response
     */
    public function deleteModel(ImageModel $image)
    {
        if ($image) {
            $fileController = new FileController;
            $fileController->deleteStorage($image->folder, $image->url);

            ImageModel::destroy($element->id);

            return response()->json('Successfully deleted', 200);
        }

        return response()->json('Image not found', 404);
    }

    /**
     * Deletes an image from storage
     * @param  string $folder
     * @param  string $url
     * @return \Illuminate\Http\Response
     */
    public function deleteStorage($folder, $url)
    {
        $fileController = new FileController;

        if ($fileController->deleteStorage($folder, $url)) {
            return response()->json('Successfully deleted', 200);
        }

        return response()->json('Error deleting image', 401);
    }

    /**
     * Sorts all images by the given positions
     * @return \Illuminate\Http\Response
     */
    public function sort()
    {
        $positions = explode(';', request()->positions);
        $images = ImageModel::get();
        for ($i = 0, $n = sizeof($positions); $i < $n; $i++) {
            foreach ($images as $image) {
                if ($image->id == $positions[$i] && $image->orderby != $i + 1) {
                    $image->orderby = $i + 1;
                    $image->save();
                }
            }
        }

        return response()->json('Successfully sorted', 200);
    }

    /**
     * Prepares the response to show the image
     * @param  string $file
     * @param  array $headers
     * @return \Illuminate\Http\Response
     */
    private function prepareResponse($file, $headers)
    {
        $response = Response::make($file, 200, $headers);

        $filetime = time();
        $etag = md5($filetime);
        $time = date('r', $filetime);
        $expires = date('r', $filetime + 3600 * 24 * 30);

        $response->setEtag($etag);
        $response->setLastModified(new \DateTime($time));
        $response->setExpires(new \DateTime($expires));
        $response->setPublic();

        if ($response->isNotModified(request())) {
            return $response;
        } else {
            $response->prepare(request());
            return $response;
        }
    }

}
