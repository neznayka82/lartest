<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(){
        $data = Product::all();
        return view('products.index', [ 'data' => $data]);
    }
    public function getall(Request $request){
        return Product::all()->toJson();
    }

    public function getbyid(Request $request){
        $product = Product::find($request->id ?? 0);
        return [
            'price' => $product->price ?? 0
        ];
    }
}
