<?php

namespace App\Http\Controllers;

use App\Models\Test;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

use Datatables;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Crypt;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Test $testModel)
    {

        //$tests = $testModel->getPublishedTests();
        Input::flash();
        return view('datatables.index');
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyData(Request $request)
    {
        $query = Test::select('*');

        $title = $request->input('title') ? $request->input('title') : null ;
        if($title)
            $query->where('title', 'LIKE', '%'.$title.'%');

        return Datatables::of($query)->make(true);
    }
    
    public function docrypt(Request $request){
        $input = $request->all();
        
        //$str = Crypt::encrypt('123');
        echo Crypt::decrypt($input['id']);
        //echo $str;
    }
    
    

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function moreInfo(Request $request)
    {
        $data = [
            'view' => view('test.ajax')->render()
        ];

        return Response::json($data, 200);

        //return "<tr><td colspan='2'>done</td></tr>";
    }

    public function unpablished(Test $testModel)
    {

        $tests = $testModel->getUnPublishedTests();

        return view('test.index', ['tests' => $tests]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('test.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Test $testModel, Request $request)
    {
        //dd($request->all());

        //$testModel->create($request->all());
        $testModel->create(array(
            'title' => Input::get('title')
        ));


        return redirect()->route('tests');
    }
     

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
