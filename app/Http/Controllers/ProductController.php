<?php

namespace App\Http\Controllers;

use App\Models\AddBranchModel;
use App\Models\BranchProductModel;
use App\Models\BranchTransferModel;
use App\Models\ProductHistoryModel;
use App\Models\ProductModel;
use App\Models\SupplierModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Traits\HandlesProductDataDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class ProductController extends Controller

{

    use HandlesProductDataDatabase;
      public function index(Request $request)
    {
        $data = $this->getProductData($request);
        return inertia('Admin/Product', $data);
    }
//    public function index(Request $request){
    
//     $Suppliers = SupplierModel::select('id','supplier_name')
//     ->where('status', 1)
//     ->orderBy('supplier_name')->get();

//     $lastCode = ProductModel::orderBy('product_code','desc')->value('product_code');
//     $nextNumber = $lastCode ? ((int) $lastCode + 1) :1;
//     $nextCode = str_pad($nextNumber,6,'0',STR_PAD_LEFT);


// $Product = ProductModel::with(['supplier', 'branchProducts'])
//  ->when($request->search, function ($query) use ($request) {
//         $search = $request->search;
//         $query->where(function ($q) use ($search) {
//             $q->where('description', 'like', "%$search%")
//               ->orWhere('product_code', 'like', "%$search%")
//               ->orWhereHas('supplier', function ($q2) use ($search) {
//                   $q2->where('supplier_name', 'like', "%$search%");
//               });
//         });
      

// })
//     ->select('id','supplier_id','account','product_code','barcode','description')
//     ->orderBy('id','desc')
//     ->paginate(perPage: 50)
//     ->withQueryString();

  
    
    

//    return Inertia::render('Admin/Product', [
//     'Product' => $Product,
//     'Suppliers' => $Suppliers,
//     'nextCode' => $nextCode,
//     'searchTerm' => $request->search,
// ]);
//    }

   public function store(Request $request){

        $validated = $request->validate([
            'supplier_id' => 'required|exists:supplier,id',
            'category_id' => 'required|exists:category,id',
            'account' => 'required|string',
             'product_code' => 'required|string|unique:product,product_code',
              'description' => 'required|string',
               'quantity' => 'required|numeric',
                'cost_price' => 'required|numeric',
                'retail_price' => 'required|numeric',
                'wholesale_price' => 'required|numeric',
                'branch_id' => 'required|numeric',
        ]);

       $barcodePath = $this->generateBarcodeAndSave($validated['product_code']);

       $product = ProductModel::create([
        'supplier_id' =>$validated['supplier_id'],
        'category_id' =>$validated['category_id'],
        'account' =>$validated['account'],
        'product_code' =>$validated['product_code'],
         'barcode' => $barcodePath,
        'description' =>$validated['description'],
       

       ]);

       $branchProduct = BranchProductModel::create([
        'branch_id' => $validated['branch_id'],
        'product_id' => $product->id,
        'quantity' => $validated['quantity'],
        'cost_price' => $validated['cost_price'],
        'retail_price' => $validated['retail_price'],
        'wholesale_price' => $validated['wholesale_price'],

       ]);
     
   $supplier = SupplierModel::with('product.branchProducts')->find($validated['supplier_id']);
    $this->logProductHistory($product, $branchProduct, 1);


$totalCost = 0;

// SAFELY loop with null checks
if ($supplier && $supplier->product) {
    foreach ($supplier->product as $prod) {
        if ($prod->branchProducts) {
            foreach ($prod->branchProducts as $bp) {
                $totalCost += $bp->cost_price * $bp->quantity;
            }
        }
    }

    $supplier->cost = $totalCost;
    $supplier->save();
}

    return back()->with('success', 'Product added and supplier cost updated.');


       
   }


public function generateBarcode($code)
{
   $generator = new BarcodeGeneratorPNG();
    $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_128);

    return response($barcode)->header('Content-Type', 'image/png');
}
public function generateBarcodeAndSave($code)
{
    $generator = new BarcodeGeneratorPNG();
    $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_128);

    $filename = 'barcode_' . $code . '.png';
    $path = 'barcodes/' . $filename;

    Storage::disk('public')->put($path, $barcode);

    return 'storage/' . $path; 
}

public function update(Request $request, $id){
$product = ProductModel::findOrFail($id);
 $account = Auth::user()->acct_name;

  $validated = $request->validate([
        'supplier_id' => 'required|exists:supplier,id',
        'category_id' => 'required|exists:category,id',
        'description' => 'required|string',
        'cost_price' => 'required|numeric',
        'retail_price' => 'required|numeric',
        'wholesale_price' => 'required|numeric',
        'branch_id' => 'required|numeric',
        'add_quantity' => 'nullable|numeric|min:0',
    ]);

     // Only update allowed fields in product
    $product->update([
        'category_id' => $validated['category_id'],
        'supplier_id' => $validated['supplier_id'],
        'description' => $validated['description'],
    ]);
     // Update branch-specific product
    $branchProduct = BranchProductModel::where('product_id', $product->id)
        ->where('branch_id', $validated['branch_id'])
        ->first();

    if ($branchProduct) {
        $newQuantity = $branchProduct->quantity;

        if (!empty($validated['add_quantity'])) {
            $newQuantity += $validated['add_quantity'];
        }

        $branchProduct->update([
            'quantity' => $newQuantity,
            'cost_price' => $validated['cost_price'],
            'retail_price' => $validated['retail_price'],
            'wholesale_price' => $validated['wholesale_price'],
        ]);
    }


      $supplier = SupplierModel::with('product.branchProducts')->find($validated['supplier_id']);

$totalCost = 0;

// SAFELY loop with null checks
if ($supplier && $supplier->product) {
    foreach ($supplier->product as $prod) {
        if ($prod->branchProducts) {
            foreach ($prod->branchProducts as $bp) {
                $totalCost += $bp->cost_price * $bp->quantity;
            }
        }
    }

    $supplier->cost = $totalCost;
    $supplier->save();
}
   
   $this->logProductHistory($product, $branchProduct, 2, $validated['add_quantity'], $account);

    return back()->with('success', 'Product updated successfully.');
    // return redirect()->to(url()->previous() . '?page=' . $request->query('page'))
    // ->with('success', 'Product updated successfully.');
}
public function destroy($id)
{
    $user = Auth::user();
    $branchId = $user->branch_id;
    $account = $user->acct_name;

    // First: find the specific branch's product
    try {
        $branchProduct = BranchProductModel::where('product_id', $id)
                                           ->where('branch_id', $branchId)
                                           ->firstOrFail();
    } catch (ModelNotFoundException $e) {
        return redirect()->back()->with('error', 'Product not found for your branch.');
    }

    // Log product history
    $product = ProductModel::findOrFail($id);
    $this->logProductHistory($product, $branchProduct, 3, null, $account);

    // Delete any transfers linked to this branch and product
    BranchTransferModel::where('product_id', $id)
        ->where(function ($q) use ($branchId) {
            $q->where('from_branch_id', $branchId)
              ->orWhere('to_branch_id', $branchId);
        })->delete();

    // Delete only the branch-specific product
    $branchProduct->delete();

    // Only delete the main product if no other branch has it
    $stillUsed = BranchProductModel::where('product_id', $id)->exists();
    if (!$stillUsed) {
        // Double check: ensure admin is deleting the last product
        $product->delete();
    }

    return redirect()->back()->with('success', 'Product deleted from branch successfully.');
}





private function logProductHistory($product, $branchProduct, $status, $updateQty = null, $account=null){

    ProductHistoryModel::create([
        'account' => $account ?? $product->account,
        'branch_id' => $branchProduct->branch_id,
        'supplier_id' => $product->supplier_id,
        'category_id' => $product->category_id,
        'product_code' => $product->product_code,
        'description' => $product->description,
        'cost_price' => $branchProduct->cost_price,
        'retail_price' => $branchProduct->retail_price,
        'wholesale_price' => $branchProduct->wholesale_price,
        'quantity' => $updateQty ?? $branchProduct->quantity,
        'status' => $status,


    ]);
}



public function findByBarcode(Request $request,$code)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $userBranchId = $user->branch_id;
     $branchId = $request->branch_id;
    $userRole = $user->acct_roles;

    $query = DB::table('product')
        ->join('branch_products', 'product.id', '=', 'branch_products.product_id')
        ->where('product.product_code', $code)
        ->select(
            'product.id as product_id',
            'product.description',
            'product.product_code',
            'branch_products.branch_id as from_branch_id',
            'branch_products.quantity',
            'branch_products.cost_price',
            'branch_products.retail_price',
            'branch_products.wholesale_price',
            
        );


       if (!in_array($userRole, [1, 2])) {
        $query->where('branch_products.branch_id', $userBranchId);
    } else {
        // For admin/manager: apply selected branch filter if provided
        if (!empty($branchId)) {
            $query->where('branch_products.branch_id', $branchId);
        }
    }

    $product = $query->first();

    if (!$product) {
        return response()->json(['error' => 'Product not found.'], 404);
    }

    return response()->json($product);
}public function processSale(Request $request, $id)
{
    $request->validate([
        'quantity' => 'required|integer|min:1',
        'cost_price' => 'required|numeric|min:0',
        'retail_price' => 'required|numeric|min:0',
        'wholesale_price' => 'required|numeric|min:0',
        'branch_id' => 'required|integer',
    ]);

    $user = Auth::user();
    $account = $user->acct_name;
    $userBranchId = $user->branch_id;
    $targetBranchId = $request->branch_id;

    // Allow only admins to process other branches
    if (!in_array($user->acct_roles, [1, 2]) && $userBranchId !== $targetBranchId) {
        return response()->json(['error' => 'Unauthorized to process this branch.'], 403);
    }

    // Get the original product
    $originalProduct = ProductModel::with('branchProducts')
        ->where('id', $id)
        ->firstOrFail();

    $branchProduct = $originalProduct->branchProducts
        ->where('branch_id', $targetBranchId)
        ->first();

    if (!$branchProduct || $branchProduct->quantity < $request->quantity) {
        return response()->json(['error' => 'Not enough quantity or invalid branch'], 400);
    }

    // Generate new product_code
    $lastCode = ProductModel::orderBy('product_code', 'desc')->value('product_code');
    $nextNumber = $lastCode ? ((int)$lastCode + 1) : 1;
    $newCode = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

    // Generate barcode
    $barcodePath = $this->generateBarcodeAndSave($newCode);

    // Create new product
    $newProduct = ProductModel::create([
        'supplier_id' => $originalProduct->supplier_id,
        'category_id' => $originalProduct->category_id,
        'account' => $account,
        'product_code' => $newCode,
        'barcode' => $barcodePath,
        'description' => 'Sales - ' . $originalProduct->description,
    ]);

    // Create new branch product
    BranchProductModel::create([
        'branch_id' => $targetBranchId, 
        'product_id' => $newProduct->id,
        'quantity' => $request->quantity,
        'cost_price' => $request->cost_price,
        'retail_price' => $request->retail_price,
        'wholesale_price' => $request->wholesale_price,
    ]);

    // Deduct from target branch product
    $branchProduct->decrement('quantity', $request->quantity);

    // Log history
    $this->logProductHistory($newProduct, $branchProduct, 2, $request->quantity, $account);

    return response()->json(['message' => 'Sale processed successfully']);
}


// =======


//         if ($userRole == 3) {
//     $query->where('branch_products.branch_id', $userBranchId);
// }
// =========
//   if (!in_array($userRole, [1, 2])) {
//         $query->where('branch_products.branch_id', $userBranchId);
//     }
// $product = $query->first();

// if (!$product) {
//     return response()->json(['error' => 'Product not found.'], 404);
// }

// return response()->json($product);
// ===========
    // // 🔒 Restrict to user's branch if acct_roles is 3
    // if ($userRole == 3) {
    //     $query->where('branch_products.branch_id', $userBranchId);
    // }

    // $products = $query->get(); // use `get()` instead of `first()` to allow multiple results

    // if ($products->isEmpty()) {
    //     return response()->json(['error' => 'Product not found or not available for this branch.'], 404);
    // }

    // return response()->json($products);
}

// public function findByBarcode($code)
// {
//     $user = Auth::user();

//     if (!$user) {
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     $userBranchId = $user->branch_id;
//     $userRokle =$user->acct_roles;

//     $product = DB::table('product')
//         ->join('branch_products', 'product.id', '=', 'branch_products.product_id')
//         ->where('product.product_code', $code)
//         ->where('branch_products.branch_id', $userBranchId)
//         ->select(
//             'product.id as product_id',
//             'product.description',
//             'product.product_code',
//             'branch_products.quantity',
//             'branch_products.cost_price',
//             'branch_products.retail_price',
//             'branch_products.wholesale_price'
//         )
//         ->first();

        

//     if (!$product) {
//         return response()->json(['error' => 'Product not found or not available for this branch.'], 404);
//     }

//     return response()->json($product);
    
// }







