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

    public function detail($id){
        if(RoomType::where('room_type_id', $id)->exists()){
            $data = RoomType::where('room_type_id', $id)->first();
            return response()->json($data);
        }
        else {
            return response()->json(['message' => 'Data not found']);
        }
    }

    public function store(Request $req){
        $validator = Validator::make($req->all(),[
            'room_type_name'=>'required',
            'price'=>'required|integer',
            'description'=>'required'
        ]);

        if($validator->fails()){
            return Response()->json($validator->errors()->toJson());
        }

        $save = RoomType::create([
            'room_type_name' => $req->get('room_type_name'),
            'price' => $req->get('price'),
            'description' => $req->get('description')
        ]);

        if($save){
            return Response()->json(['status' => true, 'message' => 'Succeed Add Room Type']);
        }
        else {
            return Response()->json(['status' => false, 'message' => 'Failed Add Room Type']);
        }
    }

    public function update($id, Request $req){
        $validator = Validator::make($req->all(),[
            'room_type_name' => 'required',
            'price' => 'required|integer',
            'description' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson());
        }

        $update = RoomType::where('room_type_id', $id)->update([
            'room_type_name' => $req->get('room_type_name'),
            'price' => $req->get('price'),
            'description' => $req->get('description')
        ]);

            if($update){
                $data = RoomType::where('room_type_id', $id)->get();
                return response()->json([
                    'status' => true,
                    'message' => 'Succeed update data',
                    'data' => $data
                ]);
            }
            else {
                return response()->json(['status' => false, 'message' => 'Failed update data']);
            }
    }

    public function uploadImage(Request $req, $id){
        $validator = Validator::make($req->all(),
        [
            'image' => 'required|image|mimes:jpeg,jpg,png'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());
        }

        $imageName = time().'.'.request()->image->getClientOriginalExtension();
        request()->image->move(public_path('roomtype_image'),$imageName);

        $update = RoomType::where('room_type_id', $id)->update(
            [
                'image' => $imageName
            ]);
        
        if($update){
            $data = RoomType::where('room_type_id', '=', $id)->get();
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Succeed upload image',
                    'data' => $data
                ]);
        }
        else {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Failed upload image'
                ]);
        }
    }

    public function destroy($id){
        $delete = RoomType::where('room_type_id', $id)->delete();
        if($delete){
            return response()->json([
                'status' => true,
                'message' => 'Succeed delete data'
            ]);
        }
        else {
            return response()->json([
                'status' => false,
                'message' => 'Failed delete data'
            ]);
        }
    }
}
