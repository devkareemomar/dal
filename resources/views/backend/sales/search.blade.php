
{{-- purchase_unit_id --}}
<div class="widget-content widget-content-area p-3"
style="overflow-y: scroll;height: 350px; background-color: #fff; border: solid 2px #d9d9d9; position: absolute; z-index: 9000000; width:350px">

    <i class="fa fa-times mb-3  p-1 close-search" style="cursor: pointer">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>

    </i>
    @if ($products->count() > 0)
    <ul class="file-tree list-group">


        @foreach ($products as $key => $row)

            <li class="list-group-item item" style="cursor: pointer;"
            item_id="{{$row->id}}"
            >{{$row->name}}</li>
        @endforeach

    </ul>

    @elseif($products->count() == 0)
    <div class="alert alert-light-danger border-0 mb-4" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <strong>Opps!</strong> No Items Matched </div>
    @endif



</div>

<script>
    $(".item").on('click',function(){

        var item_id = $(this).attr('item_id');
        $.ajax({
                type: 'get',
                url: "additem"+'/'+item_id,
                success: function (data) {

                    var db = $('.itemid').map(function (i, n) {
                               return $(n).val();
                                }).get();

                if(db.includes(item_id)){
                    alert("{{translate('this product has already been added')}}")
                }else{
                    $('.item-table').append(data);
                     $('.widget-content-area').remove();
                	calculateTotal();
                }

                    },
                error: function(data_error, exception) {
                    if(exception == 'error'){
                        var error_list = '' ;
                        $.each(data_error.responseJSON.errors, function(index,v){
                            error_list += '<li>'+v+'</li>';
                        });
                        $('.alert-errors ul').html(error_list)
                    }
                }
            });

    });

   // check is real data in input
   $(".description").focusout(function(){
        if($(this).find("[name='item_id[]']").val() == ''){
            $(this).find("input").val('');
        }
    });
    $(".close-search").on('click', function(){
        var id = {{ $id }};
        $('.widget-content-area'+id).remove();
    });
</script>



