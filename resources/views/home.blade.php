@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header"><b>Disk Usage Overview</b></div>

                    <div class="card-body">

                        <div class="row">
                            <b class="col-md-2">Total Size : </b>
                            <span class="col-md-10">{{ number_format(($gallery->sum('file_size') / 1000000), 2) }} MB  ({{ number_format($gallery->sum('file_size')) }} B)</span>
                        </div>
                        <div class="row">
                            <b class="col-md-2">No of files : </b>
                            <span class="col-md-10">{{ $gallery->count() }}</span>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <br>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header"><b>Disk Usage Compositions</b></div>

                    <div class="card-body">

                        @if (! $gallery->isEmpty())
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>No of files Size</th>
                                        <th>Size</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($gallery->groupBy('file_type')->toArray() as $file_type => $type)
                                        @php
                                            $type = collect($type);
                                        @endphp
                                        <tr>
                                            <td>{{ $file_type }}</td>
                                            <td>{{ count($type) }}</td>
                                            <td>{{ number_format(($type->sum('file_size') / 1000000), 2) }} MB  ({{ number_format($type->sum('file_size')) }} B)</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            No Data
                        @endif

                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
