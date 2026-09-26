<?php

declare(strict_types=1);

namespace App\Services\VCard;

use App\Enums\ProfileBlockType;
use App\Models\Profile;
use Illuminate\Support\Collection;

class VCard4Generator
{
    /**
     * Generate an RFC 6350 compliant vCard 4.0 string for a profile.
     *
     * @param Profile $profile
     * @param mixed $blocksOrAvatar Collection|array|string|null
     * @return string
     */
    public function generate(Profile $profile, mixed $blocksOrAvatar = null): string
    {
        $avatarUrl = is_string($blocksOrAvatar) ? $blocksOrAvatar : null;
        $blocks = ($blocksOrAvatar instanceof Collection || is_array($blocksOrAvatar))
            ? collect($blocksOrAvatar)
            : null;

        $lines = [];
        $lines[] = 'BEGIN:VCARD';
        $lines[] = 'VERSION:4.0';
        $lines[] = 'PRODID:-//AURA CARD//Digital Profile Platform v3.0//EN';

        // Full Name
        $displayName = trim((string) $profile->display_name);
        $lines[] = 'FN:' . $this->escapeText($displayName);

        // Name components (Family;Given;Additional;Prefix;Suffix)
        $parts = explode(' ', $displayName, 2);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';
        $lines[] = 'N:' . $this->escapeText($lastName) . ';' . $this->escapeText($firstName) . ';;;';

        // Organization & Title
        if (! empty($profile->company_name)) {
            $lines[] = 'ORG:' . $this->escapeText(trim((string) $profile->company_name));
        }

        if (! empty($profile->professional_title)) {
            $lines[] = 'TITLE:' . $this->escapeText(trim((string) $profile->professional_title));
        }

        // Bio / Note
        if (! empty($profile->bio)) {
            $lines[] = 'NOTE:' . $this->escapeText(trim((string) $profile->bio));
        }

        // Canonical Public Profile URL
        $profileUrl = url("/p/{$profile->slug}");
        $lines[] = 'URL:' . $this->escapeText($profileUrl);

        // Contact Actions Blocks (Phones, Emails, Websites, WhatsApp, Addresses)
        $contactBlock = $blocks
            ? $blocks->first(fn ($b) => $b->type === ProfileBlockType::CONTACT_ACTIONS || (is_object($b) && isset($b->type->value) && $b->type->value === 'contact_actions') || (is_array($b) && ($b['type'] ?? '') === 'contact_actions'))
            : ($profile->relationLoaded('blocks') ? $profile->blocks->firstWhere('type', ProfileBlockType::CONTACT_ACTIONS) : $profile->blocks()->where('type', 'contact_actions')->where('is_enabled', true)->first());

        if ($contactBlock) {
            $content = is_array($contactBlock) ? ($contactBlock['content'] ?? []) : ($contactBlock->content ?? []);
            $items = $content['items'] ?? [];

            foreach ($items as $item) {
                $type = $item['type'] ?? '';
                $val = trim((string) ($item['value'] ?? ''));

                if (empty($val)) {
                    continue;
                }

                if ($type === 'phone') {
                    $cleanVal = preg_replace('/[^\d+]/', '', $val);
                    $lines[] = 'TEL;TYPE=voice,pref:tel:' . $cleanVal;
                } elseif ($type === 'email') {
                    $lines[] = 'EMAIL;TYPE=work:' . $this->escapeText($val);
                } elseif ($type === 'whatsapp') {
                    $cleanWa = preg_replace('/[^\d+]/', '', $val);
                    $lines[] = 'TEL;TYPE=whatsapp,cell:' . $this->escapeText($cleanWa);
                } elseif ($type === 'website') {
                    $lines[] = 'URL;TYPE=work:' . $this->escapeText($val);
                } elseif ($type === 'address') {
                    $lines[] = 'ADR;TYPE=work:;;' . $this->escapeText($val) . ';;;;';
                }
            }
        }

        // If no contact block, fallback to user email
        if (empty($contactBlock) && $profile->user && ! empty($profile->user->email)) {
            $lines[] = 'EMAIL;TYPE=work:' . $this->escapeText($profile->user->email);
        }

        // Revision timestamp
        $lines[] = 'REV:' . gmdate('Ymd\THis\Z');
        $lines[] = 'END:VCARD';

        // Fold lines and terminate with CRLF per RFC 6350
        $foldedLines = array_map([$this, 'foldLine'], $lines);

        return implode("\r\n", $foldedLines) . "\r\n";
    }

    /**
     * Escape special characters in text per RFC 6350 Section 3.4.
     */
    protected function escapeText(string $text): string
    {
        $escaped = str_replace('\\', '\\\\', $text);
        $escaped = str_replace(',', '\,', $escaped);
        $escaped = str_replace(';', '\;', $escaped);
        $escaped = str_replace(["\r\n", "\r", "\n"], '\n', $escaped);

        return $escaped;
    }

    /**
     * Fold lines exceeding 75 octets per RFC 6350 Section 3.2.
     * Folding is done by inserting CRLF followed by a single ASCII space.
     */
    protected function foldLine(string $line): string
    {
        $maxOctets = 75;

        if (strlen($line) <= $maxOctets) {
            return $line;
        }

        $result = '';
        $currentLine = '';

        $chars = mb_str_split($line);

        foreach ($chars as $char) {
            $charLen = strlen($char);
            $currentLen = strlen($currentLine);

            $limit = empty($result) ? $maxOctets : ($maxOctets - 1); // 1 byte for leading space on folded lines

            if ($currentLen + $charLen > $limit) {
                if (empty($result)) {
                    $result = $currentLine;
                } else {
                    $result .= "\r\n " . $currentLine;
                }
                $currentLine = $char;
            } else {
                $currentLine .= $char;
            }
        }

        if (! empty($currentLine)) {
            if (empty($result)) {
                $result = $currentLine;
            } else {
                $result .= "\r\n " . $currentLine;
            }
        }

        return $result;
    }
}
