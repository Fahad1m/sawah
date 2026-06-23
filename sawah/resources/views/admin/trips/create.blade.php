@extends('layouts.guest')

@section('content')
  <h2>إضافة رحلة</h2>
  <div class="form-container">
    <form action="{{ route('admin.trips.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @include('admin.trips.partials.form', ['trip'=>null,'features'=>''])
      <button class="btn btn-primary">حفظ</button>
    </form>
  </div>
@endsection
