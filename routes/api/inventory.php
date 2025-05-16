<?php

use App\Http\Controllers\BidSummariesController;
use App\Http\Controllers\MaterialRequestController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequisitionNoteController;
use App\Http\Controllers\StockInwardController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Stock Inward
Route::middleware(['auth:sanctum'])->prefix('web/v1/inward')->group(function () {
    Route::post('/', [StockInwardController::class, 'index']);    
    Route::post('store', [StockInwardController::class, 'store']);
    Route::post('/get-inward-details', [StockInwardController::class, 'getInwardDetails']);
});
// POs
Route::middleware(['auth:sanctum'])->prefix('web/v1/pos')->group(function () {
    Route::post('/', [PurchaseOrderController::class, 'index']);          
    Route::post('store', [PurchaseOrderController::class, 'store']);  
    Route::post('show', [PurchaseOrderController::class, 'show']);  
    Route::post('getSingle', [PurchaseOrderController::class, 'getSingle']);  
});
// Bid Summaries
Route::middleware(['auth:sanctum'])->prefix('web/v1/bid-summaries')->group(function () {
    Route::post('/', [BidSummariesController::class, 'index']);          
    Route::post('store', [BidSummariesController::class, 'store']);       
    Route::post('/show', [BidSummariesController::class, 'show']);
    Route::post('/compareBids', [BidSummariesController::class, 'compareBid']);
    Route::post('update', [BidSummariesController::class, 'update']);   
    Route::post('delete', [BidSummariesController::class, 'delete']);   
});
// PRN
Route::middleware(['auth:sanctum'])->prefix('web/v1/prn')->group(function () {
    Route::post('/', [PurchaseRequisitionNoteController::class, 'index']);        
    Route::post('store', [PurchaseRequisitionNoteController::class, 'store']);      
    Route::post('viewPRN', [PurchaseRequisitionNoteController::class, 'view']);  
    Route::post('fetch-prn-products', [PurchaseRequisitionNoteController::class, 'fetchPrnProducts']);
});
// MR
Route::middleware(['auth:sanctum'])->prefix('web/v1/mr')->group(function () {
    Route::post('/', [MaterialRequestController::class, 'index']);        
    Route::post('store', [MaterialRequestController::class, 'store']);      
    Route::post('view', [MaterialRequestController::class, 'view']); 
    Route::post('detail-update', [MaterialRequestController::class, 'update']);
    Route::post('mr-delete', [MaterialRequestController::class, 'mr_destroy']);
    Route::post('detail-delete', [MaterialRequestController::class, 'destroy']);
});
// Products
Route::middleware(['auth:sanctum'])->prefix('web/v1/inventory-product')->group(function () {
    Route::post('/', [ProductController::class, 'index']);        
    Route::post('store', [ProductController::class, 'store']);    
    Route::post('update', [ProductController::class, 'update']);  
    Route::post('/delete', [ProductController::class, 'delete']);  
});
// Product Category
Route::middleware(['auth:sanctum'])->prefix('web/v1/inventory-product-category')->group(function () {
    Route::post('/', [ProductCategoryController::class, 'index']);        
    Route::post('store', [ProductCategoryController::class, 'store']);    
    Route::post('update', [ProductCategoryController::class, 'update']);  
    Route::post('/delete', [ProductCategoryController::class, 'delete']);  
});
// Product Unit
Route::middleware(['auth:sanctum'])->prefix('web/v1/inventory-product-unit')->group(function () {
    Route::post('/', [ProductUnitController::class, 'index']);        
    Route::post('store', [ProductUnitController::class, 'store']);    
    Route::post('update', [ProductUnitController::class, 'update']);  
    Route::post('/delete', [ProductUnitController::class, 'delete']);  
});
// Suppliers
Route::middleware(['auth:sanctum'])->prefix('web/v1/supplier')->group(function () {
    Route::post('/', [SupplierController::class, 'index']);        
    Route::post('store', [SupplierController::class, 'store']);    
    Route::post('update', [SupplierController::class, 'update']);  
    Route::post('/delete', [SupplierController::class, 'delete']);  
});
// navbar requests count
Route::middleware(['auth:sanctum'])->prefix('web/v1/allRequests')->group(function () {
    Route::post('/', [ProductController::class, 'allRequests']);
});
