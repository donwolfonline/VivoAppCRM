<div class="row">
    @foreach($response as $que => $ans)
        <div class="col-12 text-xs">
            <h6 class="text-small">{{$que}}</h6>
            <p>{{$ans}}</p>
        </div>
    @endforeach
</div>
