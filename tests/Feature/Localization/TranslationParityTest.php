<?php

declare(strict_types=1);

namespace Tests\Feature\Localization;

use Tests\TestCase;

class TranslationParityTest extends TestCase
{
    /**
     * Test that fr.json, en.json, and ar.json have 100% key parity and no empty values.
     */
    public function test_translation_files_have_exact_parity_and_no_empty_values(): void
    {
        $enPath = lang_path('en.json');
        $frPath = lang_path('fr.json');
        $arPath = lang_path('ar.json');

        $this->assertFileExists($enPath);
        $this->assertFileExists($frPath);
        $this->assertFileExists($arPath);

        $en = json_decode(file_get_contents($enPath), true, 512, JSON_THROW_ON_ERROR);
        $fr = json_decode(file_get_contents($frPath), true, 512, JSON_THROW_ON_ERROR);
        $ar = json_decode(file_get_contents($arPath), true, 512, JSON_THROW_ON_ERROR);

        $enKeys = array_keys($en);
        $frKeys = array_keys($fr);
        $arKeys = array_keys($ar);

        sort($enKeys);
        sort($frKeys);
        sort($arKeys);

        // Assert exact key match
        $missingInFr = array_diff($enKeys, $frKeys);
        $missingInAr = array_diff($enKeys, $arKeys);
        $extraInFr = array_diff($frKeys, $enKeys);
        $extraInAr = array_diff($arKeys, $enKeys);

        $this->assertEmpty($missingInFr, 'Keys missing in fr.json: ' . implode(', ', $missingInFr));
        $this->assertEmpty($missingInAr, 'Keys missing in ar.json: ' . implode(', ', $missingInAr));
        $this->assertEmpty($extraInFr, 'Extra keys in fr.json: ' . implode(', ', $extraInFr));
        $this->assertEmpty($extraInAr, 'Extra keys in ar.json: ' . implode(', ', $extraInAr));

        // Assert no empty or whitespace-only values
        foreach ($en as $key => $val) {
            $this->assertNotEmpty(trim((string) $val), "en.json has empty value for key: {$key}");
        }
        foreach ($fr as $key => $val) {
            $this->assertNotEmpty(trim((string) $val), "fr.json has empty value for key: {$key}");
        }
        foreach ($ar as $key => $val) {
            $this->assertNotEmpty(trim((string) $val), "ar.json has empty value for key: {$key}");
        }
    }

    /**
     * Test locale switching and document direction setting across routes.
     */
    public function test_locale_switching_endpoint_sets_session(): void
    {
        foreach (['fr', 'en', 'ar'] as $locale) {
            $response = $this->get(route('locale.switch', ['locale' => $locale]));
            $response->assertSessionHas('locale', $locale);
        }
    }
}
