<?php

namespace App\Http\Controllers;
use App\Libraries\WebexApi;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Super Admin']);
    }

   public function webex()
   {
        if(!config('services.webex.bearer_token')) {
            throw new HttpException(500, 'Webex bearer token not set', null, []);
        }
        $api = new WebexApi;
        $rooms = [];
        $webexRooms = $api->getRooms();
        foreach($webexRooms['items'] as $room) {
            $room['memberships'] = $api->getRoomMemberships($room['id'])['items'];
            $rooms[] = $room;
        }
        return view('admin.webex', ['rooms' => $rooms]);
   }
}