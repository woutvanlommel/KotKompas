<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => ['nl' => 'Algemeen', 'en' => 'General'],
                'faqs' => [
                    [
                        'vraag' => ['nl' => 'Hoe werkt huren via KotKompas?', 'en' => 'How does renting through KotKompas work?'],
                        'antwoord' => ['nl' => 'Je zoekt een kot in jouw stad, vergelijkt de beschikbare panden en plant rechtstreeks een bezichtiging bij de eigenaar. Alles gebeurt op één plek, zonder tussenpersoon.', 'en' => 'You search for a room in your city, compare the available places and book a viewing directly with the owner. Everything happens in one place, without a middleman.'],
                    ],
                    [
                        'vraag' => ['nl' => 'In welke steden is KotKompas beschikbaar?', 'en' => 'Which cities does KotKompas cover?'],
                        'antwoord' => ['nl' => 'We breiden voortdurend uit naar studentensteden. Vind je jouw stad nog niet terug? Laat het ons weten via de contactpagina.', 'en' => 'We are continuously expanding to student cities. Don\'t see your city yet? Let us know through the contact page.'],
                    ],
                ],
            ],
            [
                'name' => ['nl' => 'Voor huurders', 'en' => 'For tenants'],
                'faqs' => [
                    [
                        'vraag' => ['nl' => 'Wat kost KotKompas voor studenten?', 'en' => 'What does KotKompas cost for students?'],
                        'antwoord' => ['nl' => 'Niets. Zoeken, vergelijken en een bezichtiging plannen is volledig gratis voor huurders. Je betaalt geen makelaarskosten en geen verborgen kosten.', 'en' => 'Nothing. Searching, comparing and booking a viewing is completely free for tenants. There are no broker fees and no hidden costs.'],
                    ],
                    [
                        'vraag' => ['nl' => 'Betaal ik makelaarskosten?', 'en' => 'Do I pay broker fees?'],
                        'antwoord' => ['nl' => 'Nee. Je huurt rechtstreeks van de eigenaar, dus er zijn geen makelaarskosten of commissies. De huurprijs die je ziet, is de prijs die je betaalt.', 'en' => 'No. You rent directly from the owner, so there are no broker fees or commissions. The rent you see is the rent you pay.'],
                    ],
                    [
                        'vraag' => ['nl' => 'Hoe neem ik contact op met de verhuurder?', 'en' => 'How do I contact the landlord?'],
                        'antwoord' => ['nl' => 'Op elke pandpagina vind je een knop om een bezichtiging aan te vragen of een bericht te sturen. De verhuurder reageert rechtstreeks aan jou via het platform.', 'en' => 'On every listing you will find a button to request a viewing or send a message. The landlord responds to you directly through the platform.'],
                    ],
                    [
                        'vraag' => ['nl' => 'Kan ik een bezichtiging online plannen?', 'en' => 'Can I schedule a viewing online?'],
                        'antwoord' => ['nl' => 'Ja. Je kiest een moment dat past en de eigenaar bevestigt de afspraak. Je hoeft niet heen en weer te bellen.', 'en' => 'Yes. You pick a time that suits you and the owner confirms the appointment. No back-and-forth phone calls needed.'],
                    ],
                ],
            ],
            [
                'name' => ['nl' => 'Voor verhuurders', 'en' => 'For landlords'],
                'faqs' => [
                    [
                        'vraag' => ['nl' => 'Ik ben verhuurder. Hoe plaats ik mijn kot online?', 'en' => 'I am a landlord. How do I list my room?'],
                        'antwoord' => ['nl' => 'Maak een verhuurdersaccount aan, voeg je gebouw en kamers toe in het dashboard en publiceer je pand. Je beheert beschikbaarheid, foto\'s en aanvragen op één plek.', 'en' => 'Create a landlord account, add your building and rooms in the dashboard and publish your listing. You manage availability, photos and requests in one place.'],
                    ],
                    [
                        'vraag' => ['nl' => 'Wat kost het om als verhuurder een pand te plaatsen?', 'en' => 'What does it cost a landlord to list a property?'],
                        'antwoord' => ['nl' => 'Een pand toevoegen en beheren kan zonder tussenpersoon. Neem contact op via het dashboard voor de actuele voorwaarden voor verhuurders.', 'en' => 'Adding and managing a listing works without a middleman. Reach out through the dashboard for the current landlord terms.'],
                    ],
                    [
                        'vraag' => ['nl' => 'Zijn de panden op KotKompas betrouwbaar?', 'en' => 'Are the listings on KotKompas trustworthy?'],
                        'antwoord' => ['nl' => 'Panden worden door de eigenaars zelf beheerd en up-to-date gehouden. Zie je iets dat niet klopt? Meld het via de contactpagina en we kijken het na.', 'en' => 'Listings are managed and kept up to date by the owners themselves. Spot something off? Report it via the contact page and we will look into it.'],
                    ],
                ],
            ],
            [
                'name' => ['nl' => 'Account & privacy', 'en' => 'Account & privacy'],
                'faqs' => [
                    [
                        'vraag' => ['nl' => 'Hoe verwijder ik mijn account of gegevens?', 'en' => 'How do I delete my account or data?'],
                        'antwoord' => ['nl' => 'Stuur een verzoek via de contactpagina en we verwijderen je account en persoonsgegevens conform de GDPR-wetgeving.', 'en' => 'Send a request via the contact page and we will delete your account and personal data in line with GDPR.'],
                    ],
                ],
            ],
        ];

        foreach ($categories as $ci => $category) {
            $model = FaqCategory::updateOrCreate(
                ['name->nl' => $category['name']['nl']],
                ['name' => $category['name'], 'sort' => $ci + 1, 'is_active' => true],
            );

            foreach ($category['faqs'] as $fi => $faq) {
                Faq::updateOrCreate(
                    ['content->vraag->nl' => $faq['vraag']['nl']],
                    [
                        'faq_category_id' => $model->id,
                        'content' => ['vraag' => $faq['vraag'], 'antwoord' => $faq['antwoord']],
                        'sort' => $fi + 1,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
