<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Dynamische XML-sitemap: statische pagina's + alle publiek zichtbare koten.
     */
    public function index(): Response
    {
        $urls = [
            ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('rooms.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => route('faq'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => route('contact'), 'changefreq' => 'monthly', 'priority' => '0.4'],
            ['loc' => route('privacy'), 'changefreq' => 'yearly', 'priority' => '0.2'],
            ['loc' => route('cookies'), 'changefreq' => 'yearly', 'priority' => '0.2'],
            ['loc' => route('algemene-voorwaarden'), 'changefreq' => 'yearly', 'priority' => '0.2'],
        ];

        $rooms = Room::query()
            ->where('status', 'available')
            ->orderByDesc('updated_at')
            ->get(['id', 'updated_at']);

        foreach ($rooms as $room) {
            $urls[] = [
                'loc' => route('rooms.show', $room),
                'lastmod' => optional($room->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.e($url['loc']).'</loc>'."\n";
            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>'.e($url['lastmod']).'</lastmod>'."\n";
            }
            $xml .= '    <changefreq>'.$url['changefreq'].'</changefreq>'."\n";
            $xml .= '    <priority>'.$url['priority'].'</priority>'."\n";
            $xml .= '  </url>'."\n";
        }

        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
