<?php

use Illuminate\Database\Seeder;

class PublicationMarketingImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\PublicationMarketingImage::firstOrCreate([
            'image_url'=> 'https://http2.mlstatic.com/D_813439-MLA48531496018_122021-O.jpg'
        ], [
            'image_url'=> 'https://http2.mlstatic.com/D_813439-MLA48531496018_122021-O.jpg'
        ]);

        App\PublicationMarketingImage::firstOrCreate([
            'image_url'=> 'https://http2.mlstatic.com/D_711513-MLA48531298818_122021-O.jpg'
        ], [
            'image_url'=> 'https://http2.mlstatic.com/D_711513-MLA48531298818_122021-O.jpg'
        ]);

        App\PublicationMarketingImage::firstOrCreate([
            'image_url'=> 'https://http2.mlstatic.com/D_808515-MLA48531357509_122021-O.jpg'
        ], [
            'image_url'=> 'https://http2.mlstatic.com/D_808515-MLA48531357509_122021-O.jpg'
        ]);

        App\PublicationMarketingImage::firstOrCreate([
            'image_url'=> 'https://http2.mlstatic.com/D_864168-MLA48531345611_122021-O.jpg'
        ], [
            'image_url'=> 'https://http2.mlstatic.com/D_864168-MLA48531345611_122021-O.jpg'
        ]);
    }
}
