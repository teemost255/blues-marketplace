<?php
namespace App\Http\Controllers;

class PagesController extends Controller
{
    public function terms()   { return view('pages.terms'); }
    public function privacy() { return view('pages.privacy'); }
}
