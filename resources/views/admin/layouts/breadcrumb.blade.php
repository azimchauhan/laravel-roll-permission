<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">{{ isset($page_title) ? $page_title : 'Page Title' }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    @if (isset($breadcrumb) && !empty($breadcrumb))
                        @foreach ($breadcrumb as $btncrumb)
                            @if (isset($btncrumb['link']))
                                <li class="breadcrumb-item"><a href="{{ $btncrumb['link'] }}"> {{ $btncrumb['title'] }} </a></li>
                            @else
                                <li class="breadcrumb-item active">{{ $btncrumb['title'] }}</li>
                            @endif
                        @endforeach
                    @endif

                    @if (isset($btnadd) && !empty($btnadd))
                        @foreach($btnadd as $btncrum)
                            @if (isset($btncrum['link']))
                                <li class="breadcrumb-item">
                                    <a href="{{ $btncrum['link'] }}" style="background-color: lightgreen;"> {{ $btncrum['title'] }} </a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                </ol>
            </div>
        </div>
    </div>
</div>
