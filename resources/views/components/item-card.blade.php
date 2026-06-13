<article class="panel item-card">
    <a href="{{route('items.show',$item->slug)}}">
        <img class="item-image" src="{{$item->image_url}}" alt="{{$item->name}}">
        <div class="item-body">
            <div class="card-badges"><span class="badge badge-{{$item->status}}">{{$item->status_label}}</span><span class="badge badge-category">{{$item->category_label}}</span>@if($item->is_resolved)<span class="badge badge-found">Selesai</span>@endif</div>
            <div class="item-title">{{$item->name}}</div>
            <div class="reporter"><img class="avatar" src="{{$item->user->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($item->user->name)}}"><span>{{$item->user->name}}</span></div>
            <div class="item-meta"><span>Lokasi: {{$item->location}}</span><span>{{$item->reported_at->diffForHumans()}}</span></div>
        </div>
    </a>
</article>
