<?php

use App\Http\Controllers\AddBranchController;
use App\Http\Controllers\AdminHomeController;
use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductHistoryController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransferProductController;
use App\Models\AddBranchModel;
use Illuminate\Support\Facades\Route;


Route::get('/', [AuthController::class, 'login'])->name('login');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/logout',[AuthController::class,'logout'])->name('logout');
Route::post('/logout',[AuthController::class,'logout'])->name('logout');
Route::post('/login', [AuthController::class, 'authenticate']);

Route::middleware('auth')->group(function () {



//Shared Routes



// Route::get('/cashier/{branch}', [TransferProductController::class, 'index'])->name('transfer.product');
//find product code
Route::get('/barcode/find/{code}', [ProductController::class, 'findByBarcode']);
Route::get('/barcode/{code}', [ProductController::class, 'generateBarcode']);
//store at salesreport
Route::post('/salesreport', [InvoiceController::class, 'store'])->name('salesreport.store');


//product
Route::post('/admin/product',[ProductController::class,'store'])->name('product.store');
Route::delete('/admin/product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
Route::put('/admin/product/{id}',[ProductController::class,'update']);
Route::post('/product/sale/{id}', [ProductController::class, 'processSale']);




//Account 
Route::post('/admin/account',[AuthController::class,'store'])->name('account.store');
Route::put('/admin/account/{id}',[AuthController::class,'update']);
Route::delete('/admin/account/{id}', [AuthController::class, 'destroy'])->name('account.destroy');

//Branch Routes
Route::post('/admin/branch',[AddBranchController::class, 'store'])->name('branch.store');
Route::put('/admin/branch/{id}', [AddBranchController::class, 'update']);

//Supplier
Route::post('/admin/supplier',[SupplierController::class, 'store'])->name('supplier.store');
Route::put('/admin/supplier/{id}', [SupplierController::class, 'update']);

//transferProduct
Route::post('/transfer',[TransferProductController::class,'store'])->name('transfer.store');

//Expenses
Route::post('/admin/expenses',[ExpensesController::class,'store'])->name('expenses.store');
Route::put('/admin/expenses/{id}',[ExpensesController::class,'update']);



//Category
Route::post('/admin/category',[CategoryController::class, 'store'])->name('supplier.store');
Route::put('/admin/category/{id}', [CategoryController::class, 'update']);



// Route::get('/admin/pdf', [PDFController::class, 'index']);
// Route::post('/invoices/upload', [PDFController::class, 'upload']);
// Route::get('/pdf/{code}', [PDFController::class, 'generateInvoiceCode']);

  Route::post('/branch-product/update-quantity', [AddBranchController::class, 'updateQuantity']);

//admin role = 1
Route::middleware(['role:1'])->group(function(){


    //Admin Routes
Route::get('/admin/home', [AdminHomeController::class, 'index'])->name('home');
//Branch Routes
Route::get('/admin/branch', [AddBranchController::class, 'index'])->name('branch');
Route::get('/admin/branch/{branch}', [AddBranchController::class, 'show'])->name('admin.branch.show');


//Supplier Routes
Route::get('/admin/supplier', [SupplierController::class, 'index'])->name('supplier');


//category Routes
Route::get('/admin/category', [CategoryController::class, 'index'])->name('category');



//Register Account Routes
Route::get('/admin/account',[AuthController::class,'index'])->name('account');


//Product Index
Route::get('/admin/product', [ProductController::class, 'index'])->name('product');

//ProductHistory Routes
Route::get('/admin/history',[ProductHistoryController::class,'index'])->name('history');

//ProductTransfer Routes
Route::get('/admin/transfer',[TransferProductController::class,'index'])->name('transfer');

//salesreport
Route::get('/admin/salesreport',[SalesReportController::class,'index'])->name('salesreport');
Route::delete('/admin/salesreport/{id}', [SalesReportController::class, 'destroy'])->name('salesreport.destroy');

Route::get('/admin/expenses',[ExpensesController::class,'index'])->name('expenses');


});
//Administrator role = 2 
Route::middleware([ 'role:2'])->group(function(){



Route::get('/administrator/home', [AdministratorController::class, 'index'])->name('admin');
Route::get('/administrator/product', [AdministratorController::class, 'product'])->name('administrator.product');
Route::get('/administrator/history', [AdministratorController::class, 'history'])->name('administrator.history');
Route::get('/administrator/supplier', [AdministratorController::class, 'supplier'])->name('administrator.supplier');
Route::get('/administrator/branch', [AdministratorController::class, 'branch'])->name('administrator.branch');
Route::get('/administrator/register', [AdministratorController::class, 'account'])->name('register');
Route::get('/administrator/transfer', [AdministratorController::class, 'transfer'])->name('administrator.transfer');
Route::get('/administrator/salesreport', [AdministratorController::class, 'salesreport'])->name('administrator.salesreport');
Route::get('/administrator/branch/{branch}', [AdministratorController::class, 'show'])->name('administrator.branch.show');
Route::get('/administrator/expenses/', [AdministratorController::class, 'expenses'])->name('administrator.expenses');
Route::get('/administrator/category', [AdministratorController::class, 'category'])->name('administrator.category');




});

//Superviser role = 3 
Route::middleware([ 'role:3'])->group(function(){
    

//Supervisor Routes
Route::get('/supervisor/{branch}', [SupervisorController::class, 'index'])->name('supervisor');
Route::get('/supervisor/{branch}/product', [SupervisorController::class, 'product'])->name('supervisor.product');
Route::get('/supervisor/{branch}/history', [SupervisorController::class, 'history'])->name('supervisor.history');
Route::get('/supervisor/{branch}/cashier', [SupervisorController::class, 'cashier'])->name('supervisor.cashier');
Route::get('/supervisor/{branch}/account', [SupervisorController::class, 'account'])->name('supervisor.account');
Route::get('/supervisor/{branch}/transfer', [SupervisorController::class, 'transfer'])->name('supervisor.transfer');
Route::get('/supervisor/{branch}/salesreport', [SupervisorController::class, 'salesreport'])->name('supervisor.salesreport');
// Route::get('/supervisor/{branch}/expenses', [SupervisorController::class, 'expenses'])->name('supervisor.expenses');
Route::get('/supervisor/{branch}/receive', [SupervisorController::class, 'receive'])->name('supervisor.receive');
Route::get('/supervisor/{branch}/inventory', [SupervisorController::class, 'inventory'])->name('supervisor.inventory');
Route::post('/supervisor/{branch}/inventory/update', [SupervisorController::class, 'updateInventory'])
    ->name('supervisor.inventory.update');
  



});

Route::get('/supervisor/{branch}/expenses', [SupervisorController::class, 'expenses'])->name('supervisor.expenses');
Route::post('/supervisor/{branch}/expenses', [SupervisorController::class, 'storeexpenses'])->name('supervisor.storeexpenses');
    //login as cashier
Route::get('/cashier/{branch}', [CashierController::class, 'index'])->name('cashier');
Route::get('/cashier/{branch}/view', [CashierController::class, 'viewproduct'])->name('viewproduct');
// Route::get('/cashier/{branch}/receipt', [CashierController::class, 'receipt'])->name('receipt');


});//end of auth middleware













