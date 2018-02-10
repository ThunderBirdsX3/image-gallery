<?php

namespace App\Http\Controllers;

use Auth, Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Gallery;

class GalleryController extends Controller
{
    /**
     * Display gallery page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all galleries of user, For pass it to Vuejs.
        $galleries = Auth::user()->Gallery()->select('id', 'src')->get();

        return view('gallery.index', compact('galleries'));
    }

    /**
     * Store a file to storage, And save to database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|mimetypes:image/jpeg,image/png|max:10000'
        ]);

        // Generate random string for src.
        $src = null;
        do { $src = $this->generateSrc(); } while (is_null($src));

        $file_size = $request->image->getClientSize();
        $file_type = $request->image->getClientMimeType();

        /**
         * Store file to sub folder.
         * หลักการเกี่ยวกับการเกี่ยวไฟล์ ถ้าเก็บไว้ใน Folder เดียวกันเยอะๆ
         * เวลาต้องการเข้าถึงไฟล์ จะทำให้เข้าถึงได้ช้า
         */
        $file_path = str_split($request->image->hashName(), 2);
        $file_path = "$file_path[0]/$file_path[1]/$file_path[2]/";
        $file_path = $request->image->store($file_path, 'public');

        Auth::user()->Gallery()->create([
            'src' => $src,
            'file_path' => $file_path,
            'file_type' => $file_type,
            'file_size' => $file_size,
        ]);

        return response()->json(['src' => $src], 200);
    }

    /**
     * This function generate src and check, It is unique in database.
     *
     * @return null If $src is duplicate.
     * @return string $src.
     */
    private function generateSrc()
    {
        $src = str_random(13);
        $validator = Validator::make(['src' => $src], ['src' => 'unique:galleries,src']);

        return $validator->fails() ? null : $src;
    }

    /**
     * Reponse file or 404.
     *
     * @param  string  $src
     * @return \Illuminate\Http\Response
     */
    public function show($src)
    {
        $gallery = Gallery::where('src', $src)->first();

        return is_null($gallery)
            ? response()->json(['massage' => 'Not Found.'], 404)
            : response()->file(storage_path("app/public/$gallery->file_path"));
    }

    /**
     * Remove file from storage, Then delete data from database.
     *
     * @param  \App\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function destroy(Gallery $gallery)
    {
        Storage::disk('public')->delete($gallery->file_path);

        $gallery->delete();

        return response()->json(['message' => 'OK'], 200);
    }
}
