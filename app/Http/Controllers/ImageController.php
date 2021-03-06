<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageController extends Controller
{

    /**
     *
     * 显示用户头像
     *
     * @param $avatar_name
     *
     * @return \Illuminate\Http\Response
     */
    public function avatar($avatar_name)
    {
        list($user_id,$size) = explode('_',str_replace(".jpg",'',$avatar_name));
        $avatarFile = storage_path('app/'.User::getAvatarPath($user_id,$size));
        if(!is_file($avatarFile)){
            $avatarFile = public_path('static/images/default_avatar.jpg');
        }
        $image =   Image::make($avatarFile);
        $response = response()->make($image->encode('jpg'));
        $response->header('Content-Type', 'image/jpeg');
        return $response;
    }

    /**
     * 显示照片
     *
     * @param $image_name
     *
     * @return mixed
     */
    public function show($image_name)
    {
        $imageFile = storage_path('app/'.str_replace("-","/",$image_name));
        if(!is_file($imageFile)){
            abort(404);
        }
        return Image::make($imageFile)->response();

    }


    /**
     * 编辑器图片上传
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $validateRules = [
            'file' => 'required|image|',
        ];

        if($request->hasFile('file')){
            $this->validate($request,$validateRules);
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'attachments/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('local')->put($filePath,File::get($file));
            return response(route("website.image.show",['image_name'=>str_replace("/","-",$filePath)]));
        }
        return response('error');

    }

}
