<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Event;

class EventController extends Controller
{
    public function event_more($name, $id = null){
        $storageServer = '';

        if($name == "pguty"){
            DB::setDefaultConnection('pguty');
            $storageServer = env('PGUTY_STORAGE');
        }else if($name == "ks"){
            DB::setDefaultConnection('ks');
            $storageServer = env('PGUTY_KS_STORAGE');
        }else{
            return;
        }
        $storageServer .= '/storage/';

        $events = Event::orderBy('name')->get();

        $params = [
            'storageServer' => $storageServer
        ];

        if(isset($id)){
            $event = Event::where([
                ['id', $id],
            ])->get()->first();
            if($event->exists()){
                $params['event'] = $event;
            }else{
                return redirect(route('events_list'));
            }
        }

        return view('eventMore', $params);
    }

    public function events_list(Request $request, $name){
        $titleName = '';
        if($name == "pguty"){
            DB::setDefaultConnection('pguty');
            $titleName = "ПГУТИ";
        }else if($name == "ks"){
            DB::setDefaultConnection('ks');
            $titleName = "КС ПГУТИ";
        }else{
            return;
        }

        $filter = [];
        $next_query = [
            'name' => '',
            'dateFrom' => '',
            'dateTo' => '',
        ];

        if($request->input("name") != null){
            $filter[] = ["name", "like", '%' . $request->input("name") . '%'];
            $next_query['name'] = $request->input("name");
        }
        if($request->input("dateFrom") != null){
            $filter[] = ["date", ">=", $request->input("dateFrom")];
            $next_query['dateFrom'] = $request->input("dateFrom");
        }
        if($request->input("dateTo") != null){
            $filter[] = ["date", "<", $request->input("dateTo")];
            $next_query['dateTo'] = $request->input("dateTo");
        }

        $events = Event::where($filter)->orderBy("name")->paginate(17);

        return view('eventsList', [
            'events' => $events,
            'next_query' => $next_query,
            'name' => $titleName
        ]);
    }
}