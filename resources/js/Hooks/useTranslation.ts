import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export function useTranslation() {
    const { props } = usePage<PageProps>();
    const translations = props.translations || {};
    const locale = props.locale || 'fr';
    const isRtl = props.direction === 'rtl';

    const t = (key: string, defaultText?: string): string => {
        return translations[key] || defaultText || key;
    };

    return { t, locale, isRtl, direction: props.direction };
}
