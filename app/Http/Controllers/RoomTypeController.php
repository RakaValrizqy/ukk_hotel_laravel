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
            'check_out' => 'required|date'
        ]);

        if($valid->fails()){
            return response()->json($valid->errors());
        }

        $dur = $req->duration;
        $in = Carbon::parse($req->check_in);
        $out = $in->addDays($dur)->format('Y-m-d');

        // $dur = $req->duration;
        // $in = Carbon::parse($req->check_in_date);
        // $out = $in->addDays($dur);

        // $from = date('2018-01-01');
        // $to = date('2018-05-02');

        $from = date($in);
        $to = date($out);
        $startDate = Carbon::createFromFormat('Y-m-d', $req->check_in);
        $endDate = Carbon::createFromFormat('Y-m-d', $req->check_out);

        $avail = RoomType::select('room_type.room_type_name', 'room.room_number', 'detail_order.access_date')
                                ->leftJoin('room', 'room.room_type_id', 'room_type.room_type_id')
                                ->leftJoin('detail_order', 'detail_order.room_id', 'room.room_id')
                                // ->whereRaw('detail_order.access_date BETWEEN ' . $req->check_in . ' AND ' . $req->check_out . '')
                                // ->with('detail_order.access_date')
                                // ->between($in, $out)
                                // ->whereBetween('access_date', [$from, $to])
                                // ->whereBetween('access_date', [$from, $to])->get();
                                // ->whereBetween('detail_order.access_date', [$startDate, $endDate])
                                // ->whereDate('detail_order.access_date', '>=', $startDate)
                                // ->whereDate('detail_order.access_date', '<=', $endDate)                                
                                
                                // ->whereNull('detail_order.access_date')
                                ->get();

        // $avail = DB::table('room_type')
        //                     ->select('room_type.room_type_name', 'room.room_number', 'detail_order.access_date')
        //                     ->leftJoin('room', 'room_type.room_type_id', 'room.room_type_id')
        //                     ->leftJoin('detail_order', 'room.room_id', 'detail_order.room_id')
        //                     // ->whereBetween('detail_order.access_date', array($req->check_in, $req->check_out))
        //                     // ->whereBetween('detail_order.access_date', [$startDate, $endDate])
        //                     // ->whereNull('detail_order.access_date')
        //                     ->get();

        return response()->json([
            'check_in' => $req->check_in,
            'duration' => $dur,
            'check_out' => $req->check_out,
            'check_out2' => $out,
            'data' => $avail
        ]);
    }
}
