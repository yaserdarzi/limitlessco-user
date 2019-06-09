<?php

use Illuminate\Database\Seeder;

class SalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Api
        \App\Sales::create([
            'title' => 'فروشندگان کلان آنلاین',
            'desc' => 'صدها هزار خریدار خدمات توریستی و گردشگری در سطح اینترنت خدمات شما را مشاهده خواهند کرد و تبدیل به مشتریانتان خواهند شد.',
            'logo' => "API.svg",
            'type' => \App\Inside\Constants::SALES_TYPE_API,
        ]);
        //Just Kish
        \App\Sales::create([
            'title' => 'جاست کیش',
            'desc' => " تبلیغات گستره و تاثیرگذاری بالا بر روی مسافران جزیره کیش در گروه وب سایتهای جاست کیش فروش را برای محصولات شما تضمین خواهد کرد.",
            'logo' => "justkish.png",
            'type' => \App\Inside\Constants::SALES_TYPE_JUSTKISH,
        ]);
        \App\Sales::create([
            'title' => 'عاملین 	فروش',
            'desc' => "عاملین 	فروش ما در ۳۱ استان ایران آماده فروش محصولات شما خواهند بود.",
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_SELLERS,
        ]);
        //Agency
        \App\Sales::create([
            'title' => 'آژانس ها',
            'desc' => "desc",
            'logo' => "agency.svg",
            'type' => \App\Inside\Constants::SALES_TYPE_AGENCY,
        ]);
        //Percent Site
        \App\Sales::create([
            'title' => 'سایت های تخفیف گروهی',
            'desc' => " مهمترین چیز در ایجاد کمپین تخفیف اینست که به اعتبار برند خدشه وارد نشود، مدیران 		کمپین های تخفیفی و تبلیغی ارائه کمپین 	های حرفه ایی و موثر را برای شما تضمین خواهد کرد.",
            'logo' => "percent.svg",
            'type' => \App\Inside\Constants::SALES_TYPE_PERCENT_SITE,
        ]);
        //Sepehr
        \App\Sales::create([
            'title' => 'سپهر',
            'desc' => " فروش بر روی بستر سپهر برای دهها آژانس که بر روی سامانه سپهر تمایل به همکاری دارند عرضه میشود.",
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_SEPEHR,
        ]);
        //Arabic Passenger
        \App\Sales::create([
            'title' => ' مسافران عرب زبان',
            'desc' => " مسافران عرب زبان که به تازگی بازار ایران برایشان جذاب شده است می توانند مشتریان مناسبی برای تجارت شما باشند.	",
            'logo' => "arabic.svg",
            'type' => \App\Inside\Constants::SALES_TYPE_ARABIC_PASSENGER,
        ]);
        //English Passenger
        \App\Sales::create([
            'title' => ' مسافران انگلیسی زبان',
            'desc' => " مسافران انگلیسی زبان از سراسر دنیا تجارت شما را خواهند یافت و مسافران با بودجه عالی 		مشتریان بالقوه ایی برای تجارت شما خواهند بود.",
            'logo' => "english.svg",
            'type' => \App\Inside\Constants::SALES_TYPE_ENGLISH_PASSENGER,
        ]);
        //social
        \App\Sales::create([
            'title' => ' شبکه های اجتماعی',
            'desc' => " میلیون ها کاربر در سراسر وب و شبکه های اجتماعی متفاوت، می توانند تبدیل به مشتریان خدماتتان شوند، ما با استفاده از الگوریتم های شناخت کاربر و بومی سازی استانداردهای فروش سرعت رشد شما را افزایش خواهیم داد.",
            'logo' => "social.svg",
            'type' => \App\Inside\Constants::SALES_TYPE_SOCIAL,
        ]);
        //celebrity
        \App\Sales::create([
            'title' => ' سلیبرتی ها ',
            'desc' => " سلبریتی هایی که تاثیر گذارند و می توانند حس اعتماد و خرید را به صدها هزار فالوئرهای خودشان انتقال دهند.",
            'logo' => "stars.svg",
            'type' => \App\Inside\Constants::SALES_TYPE_CELEBRITY,
        ]);

    }
}
