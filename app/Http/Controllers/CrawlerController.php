<?php

namespace App\Http\Controllers;

use App\Services\CrawlerService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CrawlerController extends Controller
{
    /**
     * @return View|Factory
     */
    public function index(): Factory|View
    {
        return view('home');
    }

    /**
     * @param Request $request
     * @param CrawlerService $crawlerService
     * @return View|Factory
     * @throws ValidationException
     */
    public function process(
        Request $request,
        CrawlerService $crawlerService
    ): Factory|View {
        $request->validate([
            'url' => 'required|url',
            'pages' => 'required|numeric|max:10',
        ]);

        return view('listing', ['listing' => $crawlerService->handle($request)]);
    }
}
