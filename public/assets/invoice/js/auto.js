/**
 * Site : http:www.smarttutorials.net
 * @author muni
 */

//adds extra table rows
var i=$('.item-table tr').length;
$(".addmore").on('click',function(){
	html = '<tr>';
	html += '<td><input class="case" type="checkbox"/></td>';
	html += `<td>
    <input  type="hidden" id="item_`+i+`"   name="item_id[]" class=" form-control form-control-sm search itemid item_id`+i+`" placeholder="@lang('site.item')" autocomplete="off">
    <input type="text" id="`+i+`" class="form-control form-control-sm search title`+i+`"  autocomplete="off" required>

    <div class="content-search`+i+`"> </div>
    </td>`;
	html += '<td></td>';
	html += '<td></td>';
	html += '<td><input type="text" name="price[]" readonly id="price_'+i+'" class="form-control changesNo" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;"></td>';
	html += '<td><input type="number" name="quantity[]" id="quantity_'+i+'" class="form-control changesNo" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;"></td>';
	html += '<td><input type="text" name="total[]" readonly id="total_'+i+'" class="form-control totalLinePrice" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;"></td>';
	html += '</tr>';
	$('.item-table').append(html);
	i++;

});

//to check all checkboxes
$(document).on('change','#check_all',function(){
	$('input[class=case]:checkbox').prop("checked", $(this).is(':checked'));
});

//deletes the selected table rows
$(".delete").on('click', function() {
	$('.case:checkbox:checked').parents("tr").remove();
	$('#check_all').prop("checked", false);
	calculateTotal();
});

//autocomplete script
$(document).on('focus','.autocomplete_txt',function(){
	type = $(this).data('type');

	if(type =='productCode' )autoTypeNo=0;
	if(type =='productName' )autoTypeNo=1;

	$(this).autocomplete({
		source: function( request, response ) {
			$.ajax({
				url : 'ajax.php',
				dataType: "json",
				method: 'post',
				data: {
				   name_startsWith: request.term,
				   type: type
				},
				 success: function( data ) {
					 response( $.map( data, function( item ) {
					 	var code = item.split("|");
						return {
							label: code[autoTypeNo],
							value: code[autoTypeNo],
							data : item
						}
					}));
				}
			});
		},
		autoFocus: true,
		minLength: 0,
		select: function( event, ui ) {
			var names = ui.item.data.split("|");
			id_arr = $(this).attr('id');
	  		id = id_arr.split("_");
			$('#itemNo_'+id[1]).val(names[0]);
			$('#itemName_'+id[1]).val(names[1]);
			$('#quantity_'+id[1]).val(1);
			$('#price_'+id[1]).val(names[2]);
			$('#total_'+id[1]).val( 1*names[2] );
			calculateTotal();
		}
	});
});

//price change
$(document).on('change keyup blur','.changesNo',function(){
	id_arr = $(this).attr('id');
	id = id_arr.split("_");
	quantity = $('#quantity_'+id[1]).val();
	price = $('#price_'+id[1]).val();
	if( quantity!='' && price !='' ) $('#total_'+id[1]).val( (parseFloat(price)*parseFloat(quantity)).toFixed(2) );
	calculateTotal();
});

$(document).on('change keyup blur','#tax',function(){
	calculateTotal();
});

//total price calculation
function calculateTotal(){
	subTotal = 0 ; total = 0;
	$('.totalLinePrice').each(function(){
		if($(this).val() != '' )subTotal += parseFloat( $(this).val() );
	});
	// $('#subTotal').val( subTotal.toFixed(2));
	$('#subTotal').text(subTotal.toFixed(2));
	$('#grand_total').text( subTotal.toFixed(2));
	tax = $('#tax').val();
	shipping_cost = $('#shipping_cost').val();
	// if(tax != '' && typeof(tax) != "undefined" ){
	// 	taxAmount = subTotal * ( parseFloat(tax) /100 );
	// 	$('#taxAmount').val(taxAmount.toFixed(2));
	// 	total = subTotal + taxAmount;
	// }else{
	// 	$('#taxAmount').val(0);
	// 	total = subTotal;
	// }
		total =  parseInt(tax) + parseInt(shipping_cost) + parseInt(subTotal);

	$('.grand_total').val(total);
	$('.grand_total').text(total);
}

$(document).on('change keyup blur','#amountPaid',function(){
	calculateAmountDue();
});

//due amount calculation
function calculateAmountDue(){
	amountPaid = $('#amountPaid').val();
	total = $('#totalAftertax').val();
	if(amountPaid != '' && typeof(amountPaid) != "undefined" ){
		amountDue = parseFloat(total) - parseFloat( amountPaid );
		$('.amountDue').val( amountDue.toFixed(2) );
	}else{
		total = parseFloat(total).toFixed(2);
		$('.amountDue').val( total );
	}
}


//It restrict the non-numbers
var specialKeys = new Array();
specialKeys.push(8,46); //Backspace
function IsNumeric(e) {
    var keyCode = e.which ? e.which : e.keyCode;
    console.log( keyCode );
    var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
    return ret;
}

//datepicker
$(function () {
    $('#invoiceDate').datepicker({});
});



// $(document).ready(function(){
    // search for item  by ajax request
    $(".search").keyup(function(){
        var value =$(this).val();
        var id =$(this).attr('id');

            $.ajax({
                type: 'get',
                url: "search"+'/'+value+'/'+id,
                success: function (data) {
                    $('.content-search').html(data);

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
// });
