@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Gallery</div>

                    <div class="card-body">

                        <upload-image-component></upload-image-component>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
