@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex">
                <h6 class="m-0 font-weight-bold text-primary">
                    {{ __('Create category') }}
                </h6>
                <div class="ml-auto">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-primary">
                        <span class="icon text-white-50">
                            <i class="fa fa-home"></i>
                        </span>
                        <span class="text">{{ __('Back to categories') }}</span>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input class="form-control" id="name" type="text" name="name" value="{{ old('name') }}">
                                @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="deskripsi">Deskripsi</label>
                                <input class="form-control" id="deskripsi" type="text" name="deskripsi" value="{{ old('deskripsi') }}">
                                @error('deskripsi')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row pt-4">
                        <div class="col-12">
                            <label for="category-image">Cover image</label>
                            <br>
                            <div class="image">
                                <input type="file" name="cover" id="category-image" class="file-input-overview">
                                <span class="form-text text-muted">Image width should be 500px x 500px</span>
                            </div>
                            @error('cover')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- <div class="row">
                        <div class="col-12">
                            <label for="images">{{ __('images') }}</label>
                            <br>
                            <div class="file-loading">
                                <input type="file" name="images[]" id="category-images" class="file-input-overview" multiple="multiple">
                            </div>
                            @error('images')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div> -->

                    <div class="form-group pt-4">
                        <button class="btn btn-primary" type="submit" name="submit">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function () {
            $("#category-img").fileinput({
                theme: "fas",
                maxFileCount: 1,
                allowedFileTypes: ['image'],
                showCancel: true,
                showRemove: false,
                showUpload: false,
                overwriteInitial: false
            })
        })
    </script>
@endpush
