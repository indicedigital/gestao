<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

trait RendersProjectTab
{
    protected function renderProjectTab(string $tab, array $data): View|Response
    {
        $data['currentTab'] = $tab;

        if (request()->header('X-Partial-Load') === 'project-tab') {
            return response()
                ->view("company.projects.tabs.{$tab}", $data)
                ->header('X-Project-Tab', $tab);
        }

        return view("company.projects.{$tab}", $data);
    }
}
