<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function show(){
        $dt_type = RoomType::get();
        return Response()->json($dt_type);
    }

    public function store(Request $req){
        $validator = Validator::make($req->all(),[
            'room_type_name'=>'required',
            'price'=>'required|integer',
            'description'=>'required',
            'image'=>'required'
        ]);

        if($validator->fails()){
            return Response()->json($validator->errors()->toJson());
        }

        $save = RoomType::create([
            'room_type_name' => $req->get('room_type_name'),
            'price' => $req->get('price'),
            'description' => $req->get('description'),
            'image' => $req->get('image')
        ]);

        if($save){
            return Response()->json(['status' => true, 'message' => 'Succeed Add Room Type']);
        }
        else {
            return Response()->json(['status' => false, 'message' => 'Failed Add Room Type']);
        }
    }
}
