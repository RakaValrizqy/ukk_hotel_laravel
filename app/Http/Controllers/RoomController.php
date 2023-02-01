<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function store(Request $req){
        $validator = Validator::make($req->all(),[
            'room_number' => 'required',
            'room_type_id' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson());
        }

        $save = Room::create([
            'room_number' => $req->get('room_number'),
            'room_type_id' => $req->get('room_type_id')
        ]);

        if($save){
            return response()->json(['status' => true, 'message' => 'Succeed Add Room']);
        }
        else {
            return response()->json(['status' => false, 'message' => 'Failed Add Room']);
        }
    }

    public function show(){
        $dt = Room::get();
        return response()->json($dt);
    }

    public function detail($id){
        if(Room::where('room_id', $id)->exists()){
            $dt = Room::where('room_id', $id)->first();
            return response()->json($dt);
        }
        else {
            return response()->json(['message' => 'Data not found']);
        }
    }

    public function update($id, Request $req){
        $validator = Validator::make($req->all(),[
            'room_number' => 'required',
            'room_type_id' =>'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson());
        }

        $update = Room::where('room_id', $id)->update([
            'room_number' => $req->get('room_number'),
            'room_type_id' => $req->get('room_type_id')
        ]);

        if($update){
            $dt = Room::where('room_id', $id)->get();
            return response()->json([
                'status' => true,
                'message' => 'Succeed update data',
                'data' => $dt
            ]);
        }
        else {
            return response()->json([
                'status' => false,
                'message' => 'Failed update data'
            ]);
        }
    }

    public function destroy($id){
        $del = Room::where('room_id', $id)->delete();
        if($del){
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
