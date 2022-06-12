<?php


namespace App\Nova\Actions;

use App\Jobs\ImportFromWooCommerce;
use Illuminate\Bus\Queueable;
use Anaseqal\NovaImport\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Fields\Select;


class ProductWooImport extends Action

{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;

    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name() {
        return __('Import Woo Products');
    }

    /**
     * @return string
     */
    public function uriKey() :string
    {
        return 'import-products';
    }

    /**
     * Perform the action.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @return mixed
     */
    public function handle(ActionFields $fields)
    {
        ImportFromWooCommerce::dispatch($fields, Auth::user()->id);

        return Action::message('Import Process Dispatched Successfully!');
    }


    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Categories')->options(\App\Category::pluck('name', 'woo_category_id')),
            Select::make('Status')->options([ 'any' => 'Any', 'draft' => 'Draft', 'pending' => 'Pending', 'private' => 'Private', 'publish' => 'Publish']),
            Select::make('Type')->options([ 'simple' => 'Simple', 'grouped'=> 'Grouped', 'external' => 'External', 'variable' => 'Variable' ])
        ];
    }
}
