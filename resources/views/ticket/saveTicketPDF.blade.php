<?php
$data['shopping'] = \App\Shopping::where(['id' => intval($shopping_id)])->first();
$data['agency'] = \App\Agency::where(['id' => explode('-', $data['shopping']->customer_id)[1]])->first();
$data['shopping']->date_persian = \Morilog\Jalali\CalendarUtils::strftime('Y-m-d', strtotime($data['shopping']->date));
$data['shopping']->created_at_persian = \Morilog\Jalali\CalendarUtils::strftime('Y-m-d', strtotime($data['shopping']->created_at));
if ($data['agency']->image) {
    $data['agency']->image_thumb = url('/files/agency/thumb/' . $data['agency']->image);
    $data['agency']->image = url('/files/agency/' . $data['agency']->image);
} else {
    $data['agency']->image_thumb = url('/files/agency/defaultAvatar.svg');
    $data['agency']->image = url('/files/agency/defaultAvatar.svg');
}
$data = (array)$data;
?>
        <!DOCTYPE html>
<html>
<head>
    <title>limitlessco</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link type="text/css" href="{{url('css/main.css')}}" rel="stylesheet">
</head>
<body>
<main role="main">
    <div id="wrapper">
        <style>
            body {
                background-color: #fff;
            }
        </style>
        <div id="print__tickets">
            <div class="print__tickets--ticket">
                <div class="print__tickets__big">
                    <div class="print__tickets__big__contact">
                        <ul class="list-unstyled">
                            <li>
                                <div class="img__cnt"><img src="{{url($data['agency']['image_thumb'])}}" alt=""></div>
                                <b>نام خریدار</b>
                                <p>{{$data['shopping']['name']}}<br/> {{$data['shopping']['phone']}}</p>
                            </li>
                            <li>
                                {{--<div class="img__cnt"><img src="img/pic.jpg" alt=""></div>--}}
                                {{--<b>بلیت مجموعه</b>--}}
                                {{--<p>پاراسل مرجان<br /> 0912-569-6696</p>--}}
                            </li>
                            <li>
                                {{--<div class="img__cnt"><img src="img/pic.jpg" alt=""></div>--}}
                                {{--<b>رضا وزاوندی</b>--}}
                                <p>تاریخ صدور
                                    بلیط<br/> {{$data['shopping']['created_at_persian']}}
                                </p>
                            </li>
                        </ul>
                    </div>
                    <div class="print__tickets__big__info">
                        <h1>{{$data['shopping']['title'] . ' '.$data['shopping']['title_more']}}</h1>
                        <ul class="list-unstyled" style="margin-top: 20px;">
                            <li>
                                <img src="{{asset('images/icon2.svg')}}" alt="">
                                <span>سر رسید</span>
                                <span style="margin-right: 5px;">{{$data['shopping']['date_persian']}}</span>
                            </li>
                            <li>
                                @if($data['shopping']['start_hours'])
                                    <img src="{{asset('images/icon3.png')}}" alt="">
                                    <span>سانس</span>
                                    <span style="margin-right: 5px;"> {{$data['shopping']['start_hours'].' تا '.$data['shopping']['end_hours']}}</span>
                                @endif
                            </li>
                            <li>
                                <img src="{{asset('images/icon4.svg')}}" alt="">
                                <span>تعداد</span>
                                <span style="margin-right: 5px;">{{$data['shopping']['count']}}</span>
                            </li>
                        </ul>
                    </div>
                    <br class="clear"/>
                    <div class="print__tickets__big__barcode">
                        <div class="barcode__show" style="margin-top: -10px;">
                            <div id="qrcode1"></div>
                        </div>
                        <span class="ticket__number">
					شماره بلیت
				</span>
                        <span class="ticket__number__value">
					{{$data['shopping']['voucher']}}
				</span>
                    </div>
                    <div class="print__tickets__big__price">
                        <span>{{number_format($data['shopping']['price_all']-$data['shopping']['percent_all'])}}</span>
                        تومان
                    </div>
                </div>
                <div class="print__tickets__mini">
                    <img width="100px" src="{{url($data['agency']['image_thumb'])}}" alt=""/>

                    <span class="ticket__number">
				شماره بلیت
			</span>
                    <span class="ticket__number__value">
				{{$data['shopping']['voucher']}}
			</span>

                    <div class="barcode__show">
                        <div id="qrcode"></div>
                    </div>
                </div>
            </div>

            <div style="width: 200px; margin: auto; margin-bottom: 10px; display: flex; justify-items: center;">
                {{--                <p style="color:#ddd; margin-left: 10px; margin-top: 8px;"> Powered by limitless </p>--}}
                {{--                <img src="{{asset('files/logo/snaplogo.png')}}">--}}
            </div>
            <div class="print__tickets--help">
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <div class="print__tickets__help">
                            <h3>شرایط استرداد و کنسلی</h3>
                            <p>در صورت استرداد بلیط، با توجه به موارد زیر، شما جریمه شده و از مبلغ بازگشتی به شما کاسته
                                می شود.</p>

                            <table>
                                <thead>
                                <tr>
                                    <th>شرایط هنگام استرداد</th>
                                </tr>
                                </thead>
                                <tbody>

                                {{--                                @if($factorDetails->products->recovery)--}}
                                {{--                                    <tr>--}}
                                {{--                                        <td>{{$factorDetails->products->recovery}}</td>--}}
                                {{--                                    </tr>--}}
                                {{--                                @else--}}
                                <tr>
                                    <td>درحال حاضر شرایط جریمه استرداد برای این بلیط ثبت نگردید</td>
                                </tr>
                                {{--                                @endif--}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <div class="print__tickets__rules">
                            <h3>قوانین و مقررات</h3>
                            <ul class="list-unstyled">
                                <li><span>1</span>
                                    {{--                                    @if($factorDetails->products->rule)--}}
                                    {{--                                        {{$factorDetails->products->rule}}--}}
                                    {{--                                    @else--}}
                                    {{'درحال حاضر قوانینی برای این بلیط ثبت نگردید'}}
                                    {{--                                    @endif--}}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="{{asset('js/jquery.min.js')}}"></script>
<script src="{{asset('js/html2canvas.min.js')}}"></script>
<script src="{{asset('js/jspdf.debug.js')}}"></script>
<script src="{{asset('js/qrcode.js')}}"></script>
<script>
    $(document).ready(function () {
        // Handler for .ready() called.


        var qrcode = new QRCode("qrcode", {
            width: 127,
            height: 127,
        });
        var qrcode1 = new QRCode("qrcode1", {
            width: 93,
            height: 93,
        });

        function makeCode() {
            qrcode.makeCode('{{$data['shopping']['voucher']}}');
            qrcode1.makeCode('{{$data['shopping']['voucher']}}');
        }

        makeCode();

        var doc = new jsPDF();
        html2canvas($('#print__tickets').get(0)).then(function (canvas) {
            var base64encodedstring = canvas.toDataURL("image/jpeg", 1);
            var imgData = base64encodedstring;
            doc.addImage(imgData, 'JPEG', 5, 5, 200, 150);
            // output as blob
            let pdf = doc.output('blob');
            let data = new FormData();
            data.append('pdf', pdf);
            fetch("http://api.limitlessco.ir" + '/api/v1/save/ticket/' + "{{$shopping_id}}", {
                method: 'POST',
                body: data
            }).then(function () {
                window.close()
            });
        });

    });
</script>
</body>
</html>
