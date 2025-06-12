<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Faq;
use App\Models\Gallary;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function Faq(){
        $faq = Faq::all();
        return response()->json([
            'message' => 'FAQ successfully.',
            'data' => $faq,
        ]);
    }

    public function gallary(){
        $gallary = Gallary::all();
         return response()->json([
            'message' => 'Gallary successfully.',
            'data' => $gallary,
        ]);
    }

    public function contact(Request $request){
        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->message = $request->message;
        $contact->save();
        return response()->json([
            'message' => 'Contact successfully.',
            'data' => $contact,
        ]);
    }
}
