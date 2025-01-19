<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Film;
use Illuminate\Support\Facades\Cache;
use SimpleXMLElement;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap', 3600, function () {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

            $films = Film::all();

            foreach ($films as $film) {
                $url = $xml->addChild('url');
                $url->addChild('loc', config('app.frontend_url') . '/catalog/films/' . $film->id);
                $url->addChild('lastmod', $film->updated_at->format('Y-m-d'));
                $url->addChild('changefreq', 'daily');
                $url->addChild('priority', 1);
            }

            return $xml->asXML();
        });

        return response($xml, headers: [
            'Content-Type' => 'application/xml'
        ]);
    }
}
