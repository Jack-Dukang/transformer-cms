<?php

namespace App\Http\Controllers\Admin;

use App\Models\ArticleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ArticleController extends BaseController
{
    protected $validateRules = [
        'title' => 'required|min:5|max:255',
        'content' => 'required|min:5|max:16777215',
        'summary' => 'sometimes|max:255',
//        'tags' => 'sometimes|max:128',
        'category_id' => 'sometimes|numeric'
    ];

    //
    public function index(Request $request)
    {
        $filter = $request->all();

        $query = ArticleModel::query();

        $filter['category_id'] = $request->input('category_id', -1);


        /*提问人过滤*/
        if (isset($filter['user_id']) && $filter['user_id'] > 0) {
            $query->where('user_id', '=', $filter['user_id']);
        }

        /*问题标题过滤*/
        if (isset($filter['word']) && $filter['word']) {
            $query->where('title', 'like', '%' . $filter['word'] . '%');
        }

        /*提问时间过滤*/
        if (isset($filter['date_range']) && $filter['date_range']) {
            $query->whereBetween('created_at', explode(" - ", $filter['date_range']));
        }

        /*问题状态过滤*/
        if (isset($filter['status']) && $filter['status'] > -1) {
            $query->where('status', '=', $filter['status']);
        }

        /*分类过滤*/
        if ($filter['category_id'] > 0) {
            $query->where('category_id', '=', $filter['category_id']);
        }


        $articles = $query->orderBy('created_at', 'desc')->paginate(20);
        return view("admin.article.index")->with('articles', $articles)->with('filter', $filter);
    }

    public function create()
    {
        return view('admin.article.create');
    }

    public function store(Request $request)
    {
        $request->flash();
        $this->validate($request,$this->validateRules);
        $currentUser = Auth::user();
        $data = [
            'user_id'      => $currentUser->id,
            'category_id'      => intval($request->input('category_id',0)),
            'title'        => trim($request->input('title')),
            'content'  => ($request->input('content')),
            'summary'  => $request->input('summary'),
            'status'       => 1,
        ];

        if($request->hasFile('logo')){
            $validateRules = [
                'logo' => 'required|image',
            ];
            $this->validate($request,$validateRules);
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'articles/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('local')->put($filePath,File::get($file));
            $data['logo'] = str_replace("/","-",$filePath);
        }



        $article = ArticleModel::query()->create($data);
        if ($article){
            $message ='文章发布成功';
            return $this->success(route('admin.article.index'),$message);
        }
        return  $this->error("文章发布失败，请稍后再试",route('website.index'));
    }

    public function verify()
    {

    }

    public function destroy()
    {

    }

    public function changeCategories()
    {

    }
}
