<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Collection;
use Illuminate\View\View;

class NovaNavigationComposer
{
    const resourceGroupPriorities = [
        'Publicaciones' => 2,
        'Vendors' => 1,
        'Configuracion' => 3,
        'Logs' => 3,
        'Other' => 9999,
    ];

    /**
     * Bind data to the view.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $navigation = $this->sortGroups($view->navigation);
        $view->with('navigation', $navigation);
    }

    /**
     * @param Collection $navigation
     *
     * @return Collection
     */
    protected function sortGroups(Collection $navigation)
    {
        return collect($navigation)
            ->keys()
            ->sortBy(function ($group) {
                return self::resourceGroupPriorities[$group] ?? self::resourceGroupPriorities['Other'];
            })
            ->mapWithKeys(function ($group) use ($navigation) {
                return [$group => $navigation[$group]];
            });
    }
}