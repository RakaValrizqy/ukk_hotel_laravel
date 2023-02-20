<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            'room_type_name'=>'required|unique:room_type',
            'price'=>'required|integer',
            'description'=>'required',
            'image' => 'required|image|mimes:jpeg,jpg,png'
        ]);

        if($validator->fails()){
            return Response()->json($validator->errors()->toJson());
        }

        $imageName = time().'.'.request()->image->getClientOriginalExtension();
        request()->image->move(public_path('roomtype_image'),$imageName);

        $save = RoomType::create([
            'room_type_name' => $req->get('room_type_name'),
            'price' => $req->get('price'),
            'description' => $req->get('description'),
            'image' => $imageName,
        ]);

        if($save){
            $dt = RoomType::where('room_type_name', $req->room_type_name)->get();
            return Response()->json([
                'status' => true, 
                'message' => 'Succeed Add Room Type',
                'data' => $dt
            ]);
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

    public function filter(Request $req){
        $valid = Validator::make($req->all(),[
            'check_in' => 'required|date',
            'duration' => 'required|integer',
            'type' => 'integer'
        ]);

        if($valid->fails()){
            return response()->json($valid->errors());
        }

        $in = new Carbon($req->check_in);
        $dur = $req->duration;
        $out = $in->addDays($dur);

        $from = date($req->check_in);
        $to = date($out);

        $avail = DB::table('room')
                        ->select('room_type.*', DB::raw('count(room.room_id) as available'))
                        ->leftJoin('room_type', 'room.room_type_id', '=', 'room_type.room_type_id')
                        ->leftJoin('detail_order', function($join) use($from, $to){
                            $join->on('room.room_id', '=', 'detail_order.room_id')
                            ->whereBetween('detail_order.access_date', [$from, $to]);
                        })
                        ->where('detail_order.access_date', '=', NULL)
                        ->groupBy('room_type.room_type_id')
                        ->get();

        $room = DB::table('room_type')
                            ->select('room_type.room_type_name', 'room.room_id', 'room.room_number', 'detail_order.access_date')
                            ->leftJoin('room', 'room_type.room_type_id', 'room.room_type_id')
                            ->leftJoin('detail_order',  function($join) use($from, $to){
                                $join->on('room.room_id', '=', 'detail_order.room_id')
                                ->whereBetween('detail_order.access_date', [$from, $to]);
                            })
                            ->where('detail_order.access_date', '=', NULL)
                            ->where('room.room_type_id', '=', $req->type)
                            ->orderBy('room.room_id')
                            ->get();

        if($avail){
            return response()->json([
                'status' => true,
                'data' => $avail,
                'room' => $room
            ]);
        }
    }
}
