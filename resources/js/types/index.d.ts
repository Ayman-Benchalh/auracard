export interface User {
    id: string;
    name: string;
    email: string;
    phone: string | null;
    account_status: 'active' | 'suspended' | 'disabled';
    locale: 'ar' | 'fr' | 'en';
    email_verified_at: string | null;
    roles: string[];
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User | null;
    };
    locale: 'ar' | 'fr' | 'en';
    direction: 'ltr' | 'rtl';
    supportedLocales: string[];
    translations: Record<string, string>;
    brand: {
        name: string;
        whatsapp: string;
        email: string;
    };
    flash: {
        success?: string | null;
        error?: string | null;
        status?: string | null;
    };
};
