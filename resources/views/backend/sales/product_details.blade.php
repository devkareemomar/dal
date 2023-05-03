<tr>
    <td><input class="case" type="checkbox"/></td>

    <td>


        <input  type="hidden" id="item_1"   name="items[{{$product->id}}][id]" value="{{$product->id}}" class=" form-control form-control-sm search itemid item_id1" placeholder="@lang('site.item')" autocomplete="off">
        <span id="itemid{{$product->id}}">{{ $product->getTranslation('name') }}</span>
        {{-- <div class="content-search1"> </div> --}}
        {{-- <input type="text" value="{{ $product->getTranslation('name') }}" data-type="productName" name="itemName[]" id="itemName_1" class="form-control autocomplete_txt" autocomplete="off" readonly> --}}

    </td>
    <td>
        @if (count(json_decode($product->colors)) > 0)
            <div class="row no-gutters">

                <div class="col-sm-10">
                    <div class="aiz-radio-inline">
                        @foreach (json_decode($product->colors) as $key => $color)
                            @php
                                $color_name = \App\Models\Color::where('code', $color)->first()->name;
                            @endphp
                            <label class="aiz-megabox pl-0 mr-2" data-toggle="tooltip"
                                data-title="{{$color_name }}">
                                <input type="radio" name="items[{{$product->id}}][color]"
                                    value="{{ $color_name }}"
                                    @if ($key == 0 )
                                        checked
                                    @endif
                                    @if (in_array($color_name, explode('-',$product->variation))) checked @endif>
                                <span
                                    class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-1 mb-2">
                                    <span class="size-15px d-inline-block rounded"
                                        style="background: {{ $color }};"></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

        @endif
    </td>
    <td>
        @if ($product->choice_options != null)
        @foreach (json_decode($product->choice_options) as $key => $choice)
            <div class="row no-gutters">
                <div class="col-sm-2">
                    <div class="opacity-50 my-2">
                        {{ \App\Models\Attribute::find($choice->attribute_id)->getTranslation('name') }}:
                    </div>
                </div>
                <div class="col-sm-10">
                    <div class="aiz-radio-inline">
                        @foreach ($choice->values as $key => $value)
                            <label class="aiz-megabox pl-0 mr-2">
                                <input type="radio"
                                    name="items[{{$product->id}}][attribute]"
                                    value="{{ $value }}"
                                    @if ($key == 0 )
                                        checked
                                    @endif
                                    @if (in_array($value, explode('-',$product->variation))) checked @endif>
                                <span
                                    class=" aiz-megabox-elem rounded d-flex align-items-center justify-content-center py-2 px-3 mb-2">
                                    {{ $value }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    </td>
    <td><input type="number" required name="items[{{$product->id}}][price]" readonly value="{{$product->unit_price}}" id="price_{{$product->id}}" class="form-control changesNo" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;"></td>
    <td><input type="number" required name="items[{{$product->id}}][quantity]" value="1" id="quantity_{{$product->id}}" class="form-control changesNo" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;"></td>
    <td><input type="number" required name="items[{{$product->id}}][total]" readonly id="total_{{$product->id}}" value="{{$product->unit_price}}" class="form-control totalLinePrice" autocomplete="off" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;"></td>
</tr>
