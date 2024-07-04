<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tag;
use App\Models\Product;
use App\Models\Category;
use App\Services\ImageService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\ProductRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        abort_if(Gate::denies('product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $products = Product::with('category', 'tags', 'firstMedia')->latest()->paginate(5); 

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_if(Gate::denies('product_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categories = Category::latest()->get(['id', 'name']);
        $tags = Tag::latest()->get(['id', 'name']);

        return view('admin.products.create', compact('categories','tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        abort_if(Gate::denies('product_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
       
        if ($request->validated()){
            $product = Product::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name), // Generate slug if not provided
                'price' => $request->price, // Include price in creation
                'description' => $request->description,
                'details' => $request->details,
                'quantity' => $request->quantity, // Example for quantity
                'weight' => $request->weight, // Example for weight
                'category_id' => $request->category_id, // Example for category_id
                'status' => $request->status, // Example for status
        ]);

            //if ($request->images && count($request->images) > 0) {
            //    (new ImageService())->storeProductImages($request->images, $product);
            //}
            if ($request->hasFile('cover')) {
                $image = $request->file('cover');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/images/products', $filename);
                $product->cover = $filename; // Simpan nama file gambar ke dalam atribut 'cover'
                $product->save(); // Simpan perubahan ke database
            }
    
            $product->tags()->attach($request->tags);

            return redirect()->route('admin.products.index')->with([
                'message' => 'success created !',
                'alert-type' => 'success'
            ]);

            
        }

        return back()->with([
            'message' => 'Something was wrong, please try again later',
            'alert-type' => 'danger'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        abort_if(Gate::denies('product_view'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        abort_if(Gate::denies('product_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        
        $categories = Category::latest()->get(['id', 'name']);
        $tags = Tag::latest()->get(['id', 'name']);

        return view('admin.products.edit', compact('categories','product','tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request,Product $product)
    {
        abort_if(Gate::denies('product_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->validated()) {
            $product->update($request->except('tags', 'images', '_token'));
            $product->tags()->sync($request->tags);

            $i = $product->media()->count() + 1;

            if ($request->images && count($request->images) > 0) {
                (new ImageService())->storeProductImages($request->images, $product, $i);
            }

            return redirect()->route('admin.products.index')->with([
                'message' => 'success created !',
                'alert-type' => 'success'
            ]);
        }

        return back()->with([
            'message' => 'Something was wrong, please try again late',
            'alert-type' => 'danger'
        ]);  
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        abort_if(Gate::denies('product_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($product->media->count() > 0) {
            foreach ($product->media as $media) {
                (new ImageService())->unlinkImage($media->file_name, 'products');
                $media->delete();
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with([
            'message' => 'success deleted !',
            'alert-type' => 'danger',
            ]);
    }

    public function removeImage(Request $request)
    {
        abort_if(Gate::denies('product_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
       
        $product = Product::findOrFail($request->product_id);
        $image = $product->media()->whereId($request->image_id)->first();

        (new ImageService())->unlinkImage($image->file_name, 'products');
        $image->delete();

        return true;
    }
}
