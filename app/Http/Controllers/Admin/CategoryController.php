<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Traits\ImageUploadTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\CategoryRequest;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    use ImageUploadTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        abort_if(Gate::denies('category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categories = Category::with('parent')->withCount('products')->latest()->paginate(5); 

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_if(Gate::denies('category_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $parent_categories = Category::whereNull('category_id')->get(['id', 'name']);

        return view('admin.categories.create', compact('parent_categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        abort_if(Gate::denies('category_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Validasi input sudah dilakukan di CategoryRequest
        $validated = $request->validated();

    // $request->validate([
    //     'name' => 'required|string|max:255',
    //     'cover' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    //     'Deskripsi' => 'required|string',
    // ]);

        // Menyimpan File
        $image = NULL;
        if ($request->hasFile('cover')) {
            //$image = $this->uploadImage($request->name, $request->cover, 'categories', 268, 268);
            $image = $request->file('cover');
        $filename = time() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('public/images/categories', $filename);

        // Simpan informasi file ke database
        // Misalnya, jika Anda memiliki model Category
        // $category = new Category();
        // $category->name = $request->name; // Pastikan nama kategori diisi
        // $category->cover = $filename;
        // $category->Deskripsi = $request->Deskripsi; // Pastikan Deskripsi diisi sesuai dengan kebutuhan aplikasi Anda
        // $category->save();
        }

        Category::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'cover' => $filename,
            'Deskripsi'=> $request->Deskripsi,
        ]);

        return redirect()->route('admin.categories.index')->with([
            'message' => 'success created !',
            'alert-type' => 'success'
        ]);
        //return redirect()->back()->withErrors(['cover' => 'Failed to upload cover image.'])->withInput();
    }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        abort_if(Gate::denies('category_view'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        abort_if(Gate::denies('category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $parent_categories = Category::whereNull('category_id')->get(['id', 'name']);

        return view('admin.categories.edit', compact('parent_categories', 'category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CategoryRequest $request,Category $category)
    {
        abort_if(Gate::denies('category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Validasi input sudah dilakukan di CategoryRequest
        $validated = $request->validated();

        // // Validasi input
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'Deskripsi' => 'required|string',
        //     'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        // ]);

       // Handle file upload if provided
       //$image = null;
       if ($request->hasFile('cover')) {
        $image = $request->file('cover');
        $filename = time() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('public/images/categories', $filename);

        // Delete old image if exists
        if ($category->cover && File::exists('storage/images/categories/' . $category->cover)) {
            File::delete('storage/images/categories/' . $category->cover);
        }

        $category->cover = $filename;
    }

         // Update category information
         $category->name = $request->name;
         $category->Deskripsi = $request->Deskripsi;
 
         // Save changes
         $category->save();
 
         return redirect()->route('admin.categories.index')->with([
             'message' => 'Category updated successfully!',
             'alert-type' => 'info'
         ]);
     }
 

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        abort_if(Gate::denies('category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if($category->category_id == null) {
            foreach($category->children as $child) {
                if (File::exists('storage/images/categories/'. $child->cover)) {
                    File::delete('storage/images/categories/'. $child->cover);
                }
            }
        }

        if ($category->cover) {
            if (File::exists('storage/images/categories/'. $category->cover)) {
                File::delete('storage/images/categories/'. $category->cover);
            }
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with([
            'message' => 'success deleted !',
            'alert-type' => 'danger',
            ]);
    }
}
