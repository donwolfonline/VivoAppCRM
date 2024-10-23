@extends('layouts.admin')
@section('page-title')
    {{__('Manage Tracker')}}
@endsection

@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Time Tracker')}}</h5>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Time Tracker')}}</li>
@endsection



@push('css-page')
<link rel="stylesheet" href="{{url('public/custom_assets/libs/swiper/dist/css/swiper.min.css')}}">
    

    <style>
        .product-thumbs .swiper-slide img {
        border:2px solid transparent;
        object-fit: cover;
        cursor: pointer;
        }
        .product-thumbs .swiper-slide-active img {
        border-color: #bc4f38;
        }

        .product-slider .swiper-button-next:after,
        .product-slider .swiper-button-prev:after {
            font-size: 20px;
            color: #000;
            font-weight: bold;
        }
        .modal-dialog.modal-md {
            background-color: #fff !important;
        }

        .no-image{
            min-height: 300px;
            align-items: center;
            display: flex;
            justify-content: center;
        }
    </style>
@endpush


@section('content')
<div class="col-xl-12">
    <div class="card">
        <div class="card-header card-body table-border-style">
            <!-- <h5></h5> -->
            <div class="table-responsive">
                <table class="table" id="pc-dt-simple">
                    <thead>
                        <tr>
                           
                            <th> {{__('Description')}}</th>
                            <th> {{__('Task')}}</th>
                             <th> {{__('Project')}}</th>
                            <th> {{__('Start Time')}}</th>
                            <th> {{__('End Time')}}</th>
                            <th>{{__('Total Time')}}</th>
                            <th>{{__('Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                      
                        @foreach ($treckers as $trecker)

                            @php
                                $total_name = Utility::second_to_time($trecker->total_time);

                            @endphp
                            <tr>
                                <td>{{$trecker->name}}</td>
                                <td>{{ isset($trecker->task->title)? $trecker->task->title :''}}</td>
                                <td>{{ isset($trecker->project->title) ? $trecker->project->title :''}}</td>
                                <td>{{date("H:i:s",strtotime($trecker->start_time))}}</td>
                                <td>{{date("H:i:s",strtotime($trecker->end_time))}}</td>
                                <td>{{$total_name}}</td>
                                <td>
                                    <div class="action-btn bg-light-dark ms-2">
                                        <a href="#" class="view-images mx-3 btn btn-sm d-inline-flex align-items-center" title="{{__('View Screenshot images')}}" data-id="{{$trecker->id}}" id="track-images-{{$trecker->id}}" data-bs-toggle="tooltip" data-bs-placement="top">
                                            <span class="text-white"><i class="ti ti-slideshow"></i></span>
                                        </a>
                                    </div>
                                    <div class="action-btn bg-danger ms-2">

                                    {!! Form::open(['method' => 'DELETE', 'route' => ['timetracker.destroy', $trecker->id]]) !!}
                                        <a href="#!" class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                            <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                        </a>
                                        {!! Form::close() !!}
                                        </div>
                                    </div>
                                 
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

   

    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg ss_modale " role="document">
          <div class="modal-content image_sider_div">
            
          </div>
        </div>
    </div>

@endsection

@push('script-page')

<script src="{{url('public/custom_assets/libs/swiper/dist/js/swiper.min.js')}}"></script>

<script type="text/javascript">

    function init_slider(){
            if($(".product-left").length){
                    var productSlider = new Swiper('.product-slider', {
                        spaceBetween: 0,
                        centeredSlides: false,
                        loop:false,
                        direction: 'horizontal',
                        loopedSlides: 5,
                        navigation: {
                            nextEl: ".swiper-button-next",
                            prevEl: ".swiper-button-prev",
                        },
                        resizeObserver:true,
                    });
                var productThumbs = new Swiper('.product-thumbs', {
                    spaceBetween: 0,
                    centeredSlides: true,
                    loop: false,
                    slideToClickedSlide: true,
                    direction: 'horizontal',
                    slidesPerView: 7,
                    loopedSlides: 5,
                });
                productSlider.controller.control = productThumbs;
                productThumbs.controller.control = productSlider;
            }
        }


    $(document).on('click', '.view-images', function () {
         
            var p_url = "{{route('tracker.image.view')}}";
            var data = {
                'id': $(this).attr('data-id')
            };
            postAjax(p_url, data, function (res) {
                $('.image_sider_div').html(res);
                $('#exampleModalCenter').modal('show');   
                setTimeout(function(){
                    var total = $('.product-left').find('.product-slider').length
                    if(total > 0){
                        init_slider(); 
                    }
                
                },200);

            });
            });


            // ============================ Remove Track Image ===============================//
            $(document).on("click", '.track-image-remove', function () {
            var rid = $(this).attr('data-pid');
            $('.confirm_yes').addClass('image_remove');
            $('.confirm_yes').attr('image_id', rid);
            $('#cModal').modal('show');
            var total = $('.product-left').find('.swiper-slide').length
            });

            function removeImage(id){
                var p_url = "{{route('tracker.image.remove')}}";
                var data = {id: id};
                deleteAjax(p_url, data, function (res) {

                    if(res.flag){
                        $('#slide-thum-'+id).remove();
                        $('#slide-'+id).remove();
                        setTimeout(function(){
                            var total = $('.product-left').find('.swiper-slide').length
                            if(total > 0){
                                init_slider();
                            }else{
                                $('.product-left').html('<div class="no-image"><h5 class="text-muted">Images Not Available .</h5></div>');
                            }
                        },200);
                    }
                      
                    $('#cModal').modal('hide');
                    toastrs('success',res.msg,'success');
                });
            }

            // $(document).on("click", '.remove-track', function () {
            // var rid = $(this).attr('data-id');
            // $('.confirm_yes').addClass('t_remove');
            // $('.confirm_yes').attr('uid', rid);
            // $('#cModal').modal('show');
        // });

      
</script>
@endpush