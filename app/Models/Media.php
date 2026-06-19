<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * Custom media-model: als de kolom `external_url` gevuld is, wijst de media-rij
 * naar een externe afbeelding (een publieke URL) i.p.v. een lokaal bestand op
 * de disk. Alle URL-methodes geven dan die externe URL terug — ongeacht de
 * gevraagde conversie (webp/thumb), want voor een externe link bestaan er geen
 * lokale conversies.
 *
 * Is `external_url` leeg, dan valt alles terug op het standaardgedrag van
 * Spatie, zodat normale uploads blijven werken zoals voorheen.
 *
 * @property string|null $external_url
 */
class Media extends BaseMedia
{
    public function getUrl(string $conversionName = ''): string
    {
        if (! empty($this->external_url)) {
            return $this->external_url;
        }

        return parent::getUrl($conversionName);
    }

    public function getFullUrl(string $conversionName = ''): string
    {
        if (! empty($this->external_url)) {
            // De externe URL is al absoluut.
            return $this->external_url;
        }

        return parent::getFullUrl($conversionName);
    }

    public function getAvailableUrl(array $conversionNames): string
    {
        if (! empty($this->external_url)) {
            return $this->external_url;
        }

        return parent::getAvailableUrl($conversionNames);
    }
}
