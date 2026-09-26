<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Models\Profile;
use App\Models\ProfileBlock;
use App\Services\VCard\VCard4Generator;
use Tests\TestCase;

class VCardGeneratorTest extends TestCase
{
    public function test_vcard_generator_produces_rfc6350_compliant_vcard4(): void
    {
        $generator = new VCard4Generator();

        $profile = new Profile();
        $profile->display_name = 'Dr. Karim Bennani';
        $profile->professional_title = 'Chief of Cardiology';
        $profile->company_name = 'Rabat Heart Center';
        $profile->bio = 'Cardiovascular specialist serving patients with excellence and compassion.';
        $profile->slug = 'karim-bennani';

        $block = new ProfileBlock();
        $block->type = \App\Enums\ProfileBlockType::CONTACT_ACTIONS;
        $block->content = [
            'items' => [
                ['type' => 'phone', 'label' => 'Direct', 'value' => '+212 537 00 11 22', 'is_primary' => true],
                ['type' => 'email', 'label' => 'Office', 'value' => 'contact@rabatheart.ma'],
                ['type' => 'address', 'label' => 'Hospital Address', 'value' => 'Avenue Mohammed V, Rabat, Morocco'],
            ],
        ];

        $vcard = $generator->generate($profile, collect([$block]));

        // RFC 6350 checks
        $this->assertStringStartsWith("BEGIN:VCARD\r\nVERSION:4.0\r\n", $vcard);
        $this->assertStringEndsWith("END:VCARD\r\n", $vcard);
        $this->assertStringContainsString('FN:Dr. Karim Bennani', $vcard);
        $this->assertStringContainsString('TITLE:Chief of Cardiology', $vcard);
        $this->assertStringContainsString('ORG:Rabat Heart Center', $vcard);
        $this->assertStringContainsString('TEL;TYPE=voice,pref:tel:+212537001122', $vcard);
        $this->assertStringContainsString('EMAIL;TYPE=work:contact@rabatheart.ma', $vcard);

        // Verify all lines terminate with strict CRLF
        $lines = explode("\r\n", rtrim($vcard, "\r\n"));
        foreach ($lines as $line) {
            // Line folding check: no line should exceed 75 octets
            $this->assertLessThanOrEqual(75, strlen($line), "Line exceeds 75 octets: {$line}");
        }
    }

    public function test_vcard_escaping_and_multilingual_handling(): void
    {
        $generator = new VCard4Generator();

        $profile = new Profile();
        $profile->display_name = 'Ayman, Ahmed; Special\\Name';
        $profile->bio = "Line 1\nLine 2, with comma; and semicolon";
        $profile->slug = 'test-escaping';

        $vcard = $generator->generate($profile, collect([]));

        $this->assertStringContainsString('FN:Ayman\, Ahmed\; Special\\\\Name', $vcard);
        $this->assertStringContainsString('NOTE:Line 1\nLine 2\, with comma\; and semicolon', $vcard);
    }
}
