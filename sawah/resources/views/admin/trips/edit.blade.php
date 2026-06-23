@extends('layouts.guest')

@section('content')
  <h2>تعديل رحلة</h2>
  <div class="form-container">
    <form action="{{ route('admin.trips.update',$trip) }}" method="POST" enctype="multipart/form-data">
      @csrf @method('PUT')
      @include('admin.trips.partials.form')
      <button class="btn btn-primary">تحديث</button>
    </form>
  </div>
@endsection
