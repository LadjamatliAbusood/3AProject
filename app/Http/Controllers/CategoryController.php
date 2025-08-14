<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request){

        $Categories = CategoryModel::paginate(50);
       
        return Inertia::render('Admin/Category',[
            'Categories' => $Categories,
            
        ]);

    }


    public function store(Request $request){
        $fields = $request -> validate([
            'category_name' => 'required|string|max:225',
            'status'=> 'required|in:1,2',
        ]);

        $category = CategoryModel::create($fields);
        return back()->with('success', 'Category addedsuccessfully');
        

    }

        public function update(Request $request, $id){
        $category = CategoryModel::findOrFail($id);
        $fields = $request->validate([
              'category_name' => 'required|string|max:225',
            'status'=> 'required|in:1,2',
        ]);
        $category->update($fields);
         return back()->with('success', 'category updated successfully.');
       
    }
    



     
}
