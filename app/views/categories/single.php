@extends('layouts.main')
@section('content')
<div class="row-fluid">
  <div class="span12">
    <h3>Category {{Crypt::decrypt($category->name)}}</h3>
  </div>
</div>
<div class="row-fluid">
  <div class="span12">
    <table class="table">
      @foreach($category->transactions()->orderBy('date','DESC')->get() as $tr)
      <tr>
        <td>{{$tr->id}}</td>
        <td>{{$tr->date}}</td>
        <td>{{Crypt::decrypt($tr->description)}}</td>
        <td>{{mf($tr->amount)}}</td>
      </tr>
      @endforeach
    </table>
  </div>
</div>
@endsection