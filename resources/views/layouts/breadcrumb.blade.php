@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @if(count(Request::segments()) > 0)

            @foreach(Request::segments() as $k => $segment)
                <li class="breadcrumb-item {{ ($k + 1 == count(Request::segments())) ? 'active' : '' }}">
                    @if(($k + 1 == count(Request::segments())))

                        @php($controllerName = Request::segment(1))

                        @if(is_numeric($segment))
                            @if($controllerName == 'customer')
                                {{ ucfirst(__('breadcrumb.name', ['name' => $$controllerName->company])) }}
                            @else
                                {{ ucfirst(__('breadcrumb.name', ['name' => $$controllerName->name])) }}
                            @endif
                        @else
                            @if($controllerName == 'analysis' && Request::segment(2) == 'cost' && isset($category))
                                {{ ucfirst(__('breadcrumb.name', ['name' => $category])) }}
                            @else
                                {{ ucfirst(__('breadcrumb.' . $segment)) }}
                            @endif
                        @endif

                    @else
                        <a href="#">{{ ucfirst(__('breadcrumb.' . $segment)) }}</a>
                    @endif
                </li>
            @endforeach

        @else

            <li class="breadcrumb-item active">
                Dashboard
            </li>

        @endif
    </ol>
</nav>
@endsection
