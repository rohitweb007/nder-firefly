@extends('layouts.main')
@section('content')
<div class="row-fluid">
  <div class="span12">
    <h2>All categories</h2>
  </div>
</div>
<div class="row-fluid">
  <div class="span12">
    <table class="table">
      @foreach($categories as $cat)
      <tr>
        <td>{{HTML::link('/home/categories/' . $cat->id,Crypt::decrypt($cat->name))}}</td>
      </tr>
      @endforeach
    </table>
  </div>
</div>
@endsection