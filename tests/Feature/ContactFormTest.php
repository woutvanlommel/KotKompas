<?php

namespace Tests\Feature;

use App\Mail\SupportContactMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jan Janssens',
            'email' => 'jan@example.be',
            'subject' => 'Vraag over een kot',
            'message' => 'Ik heb een vraag over de beschikbaarheid van dit kot.',
            'consent' => 'on',
            'website' => '', // honeypot, must stay empty
        ], $overrides);
    }

    public function test_valid_submission_sends_support_mail_and_redirects_with_success(): void
    {
        Mail::fake();
        config(['mail.support_address' => 'support@kotkompas.be']);

        $this->post('/contact', $this->payload())
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(SupportContactMail::class, function (SupportContactMail $mail) {
            return $mail->hasTo('support@kotkompas.be')
                && $mail->senderEmail === 'jan@example.be'
                && $mail->senderName === 'Jan Janssens'
                && $mail->subjectLine === 'Vraag over een kot'
                && $mail->channel === 'website';
        });
    }

    public function test_website_channel_uses_website_subject_and_wording(): void
    {
        $mail = new SupportContactMail(
            senderName: 'Jan',
            senderEmail: 'jan@example.be',
            subjectLine: 'Mijn vraag',
            body: 'Een bericht via de site.',
            channel: 'website',
        );

        $this->assertSame('Contact via website: Mijn vraag', $mail->envelope()->subject);
        $mail->assertSeeInHtml('via het contactformulier op de website');
        $mail->assertDontSeeInHtml('via het dashboard');
    }

    public function test_dashboard_channel_keeps_existing_wording(): void
    {
        $mail = new SupportContactMail(
            senderName: 'Jan',
            senderEmail: 'jan@example.be',
            subjectLine: 'Mijn vraag',
            body: 'Een bericht via het dashboard.',
        );

        $this->assertSame('Contact verhuurder: Mijn vraag', $mail->envelope()->subject);
        $mail->assertSeeInHtml('via het dashboard');
    }

    public function test_honeypot_submission_is_dropped_without_sending(): void
    {
        Mail::fake();

        $this->post('/contact', $this->payload(['website' => 'http://spam.example']))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertNothingSent();
    }

    public function test_invalid_submission_does_not_send(): void
    {
        Mail::fake();

        $this->post('/contact', $this->payload(['message' => 'kort']))
            ->assertSessionHasErrors('message');

        Mail::assertNothingSent();
    }
}
