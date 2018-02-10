<?php

namespace App\Http\Controllers;

use Validator;

use Illuminate\Http\Request;

use App\User;
use App\Gallery;

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $galleries = User::find(1)->Gallery()->select('src')->get();
        return view('gallery.index', compact('galleries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|mimetypes:image/jpeg,image/png|max:10000'
        ]);

        $src = null;
        do { $src = $this->generateSrc(); } while (is_null($src));

        $file_size = $request->image->getClientSize();
        $file_type = $request->image->getClientMimeType();

        $file_path = str_split($request->image->hashName(), 2);
        $file_path = "$file_path[0]/$file_path[1]/$file_path[2]/";
        $file_path = $request->image->store($file_path, 'public');

        User::find(1)->Gallery()->create([
            'src' => $src,
            'file_path' => $file_path,
            'file_type' => $file_type,
            'file_size' => $file_size,
        ]);

        return response()->json(['src' => $src], 200);
    }

    /**
     * This function generate link and check, It is unique in database.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
