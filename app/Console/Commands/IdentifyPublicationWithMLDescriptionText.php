<?php

namespace App\Console\Commands;

use App\Connectors\MercadolibreConnector;
use App\Jobs\SyncProductsToTiendanubeJob;
use App\Publication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IdentifyPublicationWithMLDescriptionText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:identify_publication_ml_text_in_description';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $publications = Publication::where('description', 'like', '%PUEDE RETIRAR EN%')->whereNotNull('tiendanube_id')->get();


        foreach ($publications as $publication)
        {
            $string = $publication->description;
            $start = null;
            if($index = strpos($string,"______________________",-0))
            {
                $start = $index -5;
            } else {
                $index = strpos($string,"PUEDE RETIRAR EN",-0);
                $start = $index -15;

            }

            $endIndex =strpos($string,"18.00hs",-0);
            $endIndex = $endIndex+11;
            $length = $endIndex-$start;

            $publication->description = substr_replace($string,'',$start,$length);;
            $publication->save();

            SyncProductsToTiendanubeJob::dispatch($publication);  //Disable TN JOB
        }

//        // load missing description
//        $mercadolibreConnector = new MercadolibreConnector();
//        $publications = Publication::whereNotNull('tiendanube_id')->whereNotNull('mla')->whereNull('description')->get();
//
//        foreach ($publications as $publication)
//        {
//            $mercadolibreProductDescription = $mercadolibreConnector->getProductDescription($publication->mla);
//            if ($mercadolibreProductDescription->successful())
//            {
//                $mlaPublicationDescription =  $mercadolibreProductDescription['plain_text'];
//
//                $textsThatNeedsToRemove = ["\n___________________________________________________\n\n********* PUEDE RETIRAR EN NUESTRO LOCAL A LAS 24 horas hábiles DESPUÉS DE CONCRETADA LA COMPRA************\n\nEmitimos Facturas Tipo A y B\n\nSe realizan envíos a toda la Argentina\n\n** Nuestro Horario de atención es de Lunes a Viernes de 10.00 a las 18.00hs **",
//                    "\n\n********* PUEDE RETIRAR EN NUESTRO LOCAL A LAS 24 horas hábiles DESPUÉS DE CONCRETADA LA COMPRA************\n\nEmitimos Facturas Tipo A y B\n\nSe realizan envíos a toda la Argentina\n\n** Nuestro Horario de atención es de Lunes a Viernes de 10.00 a las 18.00hs **",
//                    "\n___________________________________________________\n********* PUEDE RETIRAR EN NUESTRO LOCAL A LAS 24 horas hábiles DESPUÉS DE CONCRETADA LA COMPRA************\nEmitimos Facturas Tipo A y B\n\nSe realizan envíos a toda la Argentina\n** Nuestro Horario de atención es de Lunes a Viernes de 10.00 a las 18.00hs **",
//                    "\n\n___________________________________________________\n\n********* PUEDE RETIRAR EN NUESTRO LOCAL A LAS 24 horas hábiles DESPUÉS DE CONCRETADA LA COMPRA************\n\nEmitimos Facturas Tipo A y B\n\nSe realizan envíos a toda la Argentina\n\n** Nuestro Horario de atención es de Lunes a Viernes de 10.00 a las 18.00hs **"];
//
//
//                foreach ($textsThatNeedsToRemove as $text)
//                {
//                    $mlaPublicationDescription =  str_replace($text,'',$mlaPublicationDescription);
//                }
//
//                $publication->description = $mlaPublicationDescription;
//                $publication->save();
//                SyncProductsToTiendanubeJob::dispatch($publication);
//            }
//
//        }



    }
}
