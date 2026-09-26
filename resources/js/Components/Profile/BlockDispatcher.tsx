import React, { useState } from 'react';
import {
    Phone,
    Mail,
    MessageCircle,
    Send,
    MapPin,
    Globe,
    Share2,
    Calendar,
    Download,
    Award,
    ExternalLink,
    Clock,
    Star,
    CheckCircle2,
    Sparkles,
    Briefcase,
    FileText,
    Camera,
    FolderGit2,
    Tag,
    ShoppingBag,
    Inbox,
    QrCode,
    FileDown,
    ArrowUpRight,
    HelpCircle,
    ChevronDown,
    Video,
    Music,
} from 'lucide-react';

export interface BlockData {
    id: string;
    type: string;
    title?: string;
    order_index: number;
    is_visible: boolean;
    config?: Record<string, any>;
    content?: Record<string, any>;
}

interface BlockDispatcherProps {
    block: BlockData;
    themeTokens?: Record<string, string>;
    primaryColor?: string;
    secondaryColor?: string;
    profileSlug?: string;
}

export const BlockDispatcher: React.FC<BlockDispatcherProps> = ({
    block,
    themeTokens,
    primaryColor = '#1e293b',
    secondaryColor = '#3b82f6',
    profileSlug,
}) => {
    const content = block.content || {};
    const config = block.config || {};
    const title = block.title;
    const [faqOpen, setFaqOpen] = useState<number | null>(null);

    switch (block.type) {
        case 'contact_actions':
        case 'contact_details': {
            const items = (content.items as any[]) || [];
            return (
                <div className="space-y-3">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">{title}</h3>}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        {items.map((item, idx) => {
                            let icon = <Globe className="w-4 h-4" />;
                            let href = item.value;
                            if (item.type === 'phone') {
                                icon = <Phone className="w-4 h-4 text-emerald-500" />;
                                href = `tel:${item.value}`;
                            } else if (item.type === 'email') {
                                icon = <Mail className="w-4 h-4 text-sky-500" />;
                                href = `mailto:${item.value}`;
                            } else if (item.type === 'whatsapp') {
                                icon = <MessageCircle className="w-4 h-4 text-emerald-600" />;
                                const clean = item.value.replace(/[^0-9]/g, '');
                                href = `https://wa.me/${clean}`;
                            } else if (item.type === 'telegram') {
                                icon = <Send className="w-4 h-4 text-blue-500" />;
                                href = `https://t.me/${item.value.replace('@', '')}`;
                            } else if (item.type === 'address') {
                                icon = <MapPin className="w-4 h-4 text-rose-500" />;
                                href = `https://maps.google.com/?q=${encodeURIComponent(item.value)}`;
                            }

                            return (
                                <a
                                    key={idx}
                                    href={href}
                                    target={item.type === 'phone' || item.type === 'email' ? undefined : '_blank'}
                                    rel="noreferrer"
                                    className="flex items-center gap-3 p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 shadow-sm transition-all group"
                                >
                                    <div className="p-2 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 group-hover:scale-105 transition-transform">
                                        {icon}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p className="text-[11px] font-medium text-slate-400 dark:text-slate-500 truncate">{item.label}</p>
                                        <p className="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{item.value}</p>
                                    </div>
                                    <ExternalLink className="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 opacity-0 group-hover:opacity-100 transition-opacity" />
                                </a>
                            );
                        })}
                    </div>
                </div>
            );
        }

        case 'social_links': {
            const items = (content.items as any[]) || [];
            return (
                <div className="space-y-3">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">{title}</h3>}
                    <div className="flex flex-wrap gap-2.5">
                        {items.map((item, idx) => (
                            <a
                                key={idx}
                                href={item.url}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-indigo-500 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-sm transition-all"
                            >
                                <Share2 className="w-3.5 h-3.5 text-indigo-500" />
                                <span className="capitalize">{item.platform}</span>
                                {item.username && <span className="text-[10px] text-slate-400 font-normal">@{item.username}</span>}
                            </a>
                        ))}
                    </div>
                </div>
            );
        }

        case 'bio':
        case 'about_bio': {
            return (
                <div className="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{title}</h3>}
                    {content.headline && (
                        <h4 className="font-extrabold text-sm text-slate-900 dark:text-white">{content.headline}</h4>
                    )}
                    <p className="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                        {content.bio}
                    </p>
                </div>
            );
        }

        case 'services':
        case 'services_list': {
            const items = (content.items as any[]) || [];
            return (
                <div className="space-y-3">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">{title}</h3>}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {items.map((srv, idx) => (
                            <div key={idx} className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1.5">
                                <div className="flex items-start justify-between gap-2">
                                    <h4 className="font-bold text-xs text-slate-900 dark:text-white">{srv.name}</h4>
                                    {srv.price && (
                                        <span className="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold">
                                            {srv.price}
                                        </span>
                                    )}
                                </div>
                                {srv.description && (
                                    <p className="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{srv.description}</p>
                                )}
                                {srv.action_url && (
                                    <a
                                        href={srv.action_url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline pt-1"
                                    >
                                        <span>Learn More</span>
                                        <ArrowUpRight className="w-3 h-3" />
                                    </a>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            );
        }

        case 'portfolio':
        case 'projects': {
            const items = (content.items as any[]) || [];
            return (
                <div className="space-y-3">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">{title}</h3>}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {items.map((item, idx) => (
                            <div key={idx} className="rounded-2xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                                {item.image_url && (
                                    <img src={item.image_url} alt={item.title} className="w-full h-32 object-cover" />
                                )}
                                <div className="p-3.5 space-y-1">
                                    <h4 className="font-bold text-xs text-slate-900 dark:text-white">{item.title}</h4>
                                    {item.subtitle && <p className="text-[10px] text-indigo-500 font-semibold">{item.subtitle}</p>}
                                    {item.description && (
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2">{item.description}</p>
                                    )}
                                    {(item.link_url || item.url) && (
                                        <a
                                            href={item.link_url || item.url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 pt-1"
                                        >
                                            <span>View Project</span>
                                            <ExternalLink className="w-3 h-3" />
                                        </a>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        }

        case 'products':
        case 'product_catalog': {
            const items = (content.items as any[]) || [];
            return (
                <div className="space-y-3">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">{title}</h3>}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {items.map((prod, idx) => (
                            <div key={idx} className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
                                <div className="flex items-center justify-between">
                                    <h4 className="font-bold text-xs text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <ShoppingBag className="w-3.5 h-3.5 text-indigo-500" />
                                        <span>{prod.title}</span>
                                    </h4>
                                    <span className="font-black text-xs text-emerald-600 dark:text-emerald-400">{prod.price}</span>
                                </div>
                                {Array.isArray(prod.features) && prod.features.length > 0 && (
                                    <ul className="space-y-1 text-[11px] text-slate-500 dark:text-slate-400">
                                        {prod.features.map((feat: string, fIdx: number) => (
                                            <li key={fIdx} className="flex items-center gap-1.5">
                                                <CheckCircle2 className="w-3 h-3 text-emerald-500 flex-shrink-0" />
                                                <span className="truncate">{feat}</span>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            );
        }

        case 'gallery':
        case 'photo_gallery': {
            const items = (content.items as any[]) || [];
            return (
                <div className="space-y-3">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">{title}</h3>}
                    <div className="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        {items.map((img, idx) => (
                            <div key={idx} className="rounded-xl overflow-hidden aspect-square border border-slate-200 dark:border-slate-800 group relative">
                                <img src={img.image_url} alt={img.caption || 'Gallery photo'} className="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                                {img.caption && (
                                    <div className="absolute inset-x-0 bottom-0 bg-black/60 p-1.5 text-[10px] text-white text-center truncate">
                                        {img.caption}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            );
        }

        case 'documents':
        case 'document_attachment': {
            const items = (content.items as any[]) || [];
            return (
                <div className="space-y-3">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">{title}</h3>}
                    <div className="space-y-2">
                        {items.map((doc, idx) => (
                            <a
                                key={idx}
                                href={doc.file_url}
                                target="_blank"
                                rel="noreferrer"
                                className="flex items-center justify-between p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-indigo-500 shadow-sm transition-all"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 flex items-center justify-center font-bold text-xs">
                                        PDF
                                    </div>
                                    <div>
                                        <p className="text-xs font-bold text-slate-900 dark:text-white truncate">{doc.title}</p>
                                        <p className="text-[10px] text-slate-400">{doc.file_size_formatted || 'Document Download'}</p>
                                    </div>
                                </div>
                                <FileDown className="w-4 h-4 text-slate-400 hover:text-indigo-600" />
                            </a>
                        ))}
                    </div>
                </div>
            );
        }

        case 'business_hours': {
            const days = (content.days as any[]) || [];
            return (
                <div className="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                    <div className="flex items-center gap-2">
                        <Clock className="w-4 h-4 text-indigo-500" />
                        <h3 className="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            {title || 'Business Hours'}
                        </h3>
                    </div>
                    <div className="space-y-1.5 text-xs">
                        {days.map((d, idx) => (
                            <div key={idx} className="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800/60 last:border-0">
                                <span className="capitalize text-slate-500 dark:text-slate-400">{d.day}</span>
                                <span className="font-semibold text-slate-800 dark:text-slate-200">
                                    {d.is_closed ? <span className="text-rose-500">Closed</span> : `${d.open} - ${d.close}`}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            );
        }

        case 'location':
        case 'map_location': {
            return (
                <div className="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
                    <div className="flex items-center gap-2">
                        <MapPin className="w-4 h-4 text-rose-500" />
                        <h3 className="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            {title || 'Address & Location'}
                        </h3>
                    </div>
                    <p className="text-xs text-slate-600 dark:text-slate-300">{content.address}</p>
                    <a
                        href={`https://maps.google.com/?q=${encodeURIComponent(content.address || '')}`}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline pt-1"
                    >
                        <span>Open in Google Maps</span>
                        <ExternalLink className="w-3 h-3" />
                    </a>
                </div>
            );
        }

        case 'testimonials':
        case 'customer_reviews': {
            const items = (content.items as any[]) || [];
            return (
                <div className="space-y-3">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">{title}</h3>}
                    <div className="space-y-2.5">
                        {items.map((test, idx) => (
                            <div key={idx} className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
                                <div className="flex gap-1 text-amber-400">
                                    {[...Array(test.rating || 5)].map((_, i) => (
                                        <Star key={i} className="w-3 h-3 fill-current" />
                                    ))}
                                </div>
                                <p className="text-xs italic text-slate-600 dark:text-slate-300 leading-relaxed">"{test.content}"</p>
                                <div>
                                    <p className="text-xs font-bold text-slate-900 dark:text-white">{test.author}</p>
                                    {(test.role || test.company) && (
                                        <p className="text-[10px] text-slate-400">{test.role} {test.company ? `• ${test.company}` : ''}</p>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        }

        case 'booking':
        case 'appointment_booking': {
            return (
                <div className="p-5 rounded-3xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white space-y-3 shadow-lg">
                    <div className="flex items-center gap-2">
                        <Calendar className="w-5 h-5 text-blue-200" />
                        <h3 className="font-extrabold text-sm">{title || 'Schedule an Appointment'}</h3>
                    </div>
                    {content.instructions && (
                        <p className="text-xs text-blue-100 leading-relaxed">{content.instructions}</p>
                    )}
                    <a
                        href={content.booking_url}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex items-center justify-center w-full py-2.5 rounded-xl bg-white text-blue-900 font-bold text-xs hover:bg-white/90 transition-colors"
                    >
                        {content.button_text || 'Book Now'}
                    </a>
                </div>
            );
        }

        case 'lead_form':
        case 'contact_form': {
            return (
                <InteractiveLeadForm
                    content={content}
                    title={title}
                    profileSlug={profileSlug}
                    primaryColor={primaryColor}
                />
            );
        }

        case 'skills': {
            const items = (content.items as any[]) || [];
            return (
                <div className="space-y-3">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">{title}</h3>}
                    <div className="flex flex-wrap gap-2">
                        {items.map((skill, idx) => (
                            <span
                                key={idx}
                                className="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-semibold flex items-center gap-1.5"
                            >
                                <CheckCircle2 className="w-3.5 h-3.5 text-emerald-500" />
                                <span>{skill.name}</span>
                                {skill.level && <span className="text-slate-400 font-normal">({skill.level})</span>}
                            </span>
                        ))}
                    </div>
                </div>
            );
        }

        case 'certifications': {
            const items = (content.items as any[]) || [];
            return (
                <div className="space-y-3">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">{title}</h3>}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        {items.map((badge, idx) => (
                            <div key={idx} className="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-3">
                                <Award className="w-5 h-5 text-amber-500 flex-shrink-0" />
                                <div className="min-w-0">
                                    <p className="text-xs font-bold truncate text-slate-900 dark:text-white">{badge.title}</p>
                                    {badge.issuer && <p className="text-[10px] text-slate-400 truncate">{badge.issuer}</p>}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        }

        case 'custom_cta':
        case 'custom_links':
        case 'rich_text': {
            if (content.url && content.button_text) {
                return (
                    <a
                        href={content.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex items-center justify-center gap-2 w-full py-3.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-colors"
                    >
                        <span>{content.button_text}</span>
                        <ArrowUpRight className="w-4 h-4" />
                    </a>
                );
            }
            if (content.html) {
                return (
                    <div
                        className="p-4 rounded-2xl bg-white/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 text-xs overflow-x-auto"
                        dangerouslySetInnerHTML={{ __html: content.html }}
                    />
                );
            }
            return null;
        }

        case 'vcard':
        case 'vcard_download':
        case 'qr_code_share': {
            return (
                <div className="p-4 rounded-3xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-900/60 flex items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center">
                            <Download className="w-5 h-5" />
                        </div>
                        <div>
                            <h4 className="font-bold text-xs text-slate-900 dark:text-white">{title || 'Save to Address Book'}</h4>
                            <p className="text-[10px] text-slate-500">RFC 6350 vCard 4.0 Standard</p>
                        </div>
                    </div>
                    <button type="button" className="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition-colors">
                        Download
                    </button>
                </div>
            );
        }

        case 'header': {
            return (
                <div className="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-center space-y-2">
                    {content.image_url && (
                        <img src={content.image_url} alt={content.headline || 'Profile Header'} className="w-20 h-20 rounded-full mx-auto object-cover border-2 border-indigo-500" />
                    )}
                    {content.headline && <h3 className="font-bold text-sm text-slate-900 dark:text-white">{content.headline}</h3>}
                    {content.subtitle && <p className="text-xs text-slate-500">{content.subtitle}</p>}
                </div>
            );
        }

        case 'faq_accordion': {
            const items = (content.items as any[]) || [];
            return (
                <div className="space-y-3">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">{title}</h3>}
                    <div className="space-y-2">
                        {items.map((faq, idx) => (
                            <details key={idx} className="group p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                                <summary className="font-bold text-xs text-slate-900 dark:text-white flex items-center justify-between cursor-pointer list-none">
                                    <span className="flex items-center gap-2">
                                        <HelpCircle className="w-3.5 h-3.5 text-indigo-500" />
                                        {faq.question}
                                    </span>
                                    <ChevronDown className="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform" />
                                </summary>
                                <p className="mt-2 text-xs text-slate-500 dark:text-slate-400 leading-relaxed pt-2 border-t border-slate-100 dark:border-slate-800">
                                    {faq.answer}
                                </p>
                            </details>
                        ))}
                    </div>
                </div>
            );
        }

        case 'video_embed': {
            return (
                <div className="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2">
                    {title && <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">{title}</h3>}
                    {content.url && (
                        <div className="aspect-video w-full rounded-2xl overflow-hidden bg-black/10">
                            <iframe
                                src={content.url}
                                title={title || 'Video Embed'}
                                className="w-full h-full border-0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowFullScreen
                            />
                        </div>
                    )}
                </div>
            );
        }

        case 'audio_player': {
            return (
                <div className="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2">
                    {title && <h3 className="text-xs font-bold text-slate-900 dark:text-white">{title}</h3>}
                    {content.audio_url && (
                        <audio controls className="w-full h-10 rounded-xl" src={content.audio_url}>
                            Your browser does not support audio playback.
                        </audio>
                    )}
                </div>
            );
        }

        default:
            return null;
    }
};

const InteractiveLeadForm: React.FC<{
    content: Record<string, any>;
    title?: string;
    profileSlug?: string;
    primaryColor: string;
}> = ({ content, title, profileSlug, primaryColor }) => {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [phone, setPhone] = useState('');
    const [message, setMessage] = useState('');
    const [websiteTrap, setWebsiteTrap] = useState('');
    const [submitted, setSubmitted] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!profileSlug) return;
        setLoading(true);
        setError(null);
        try {
            const res = await fetch(`/p/${profileSlug}/contact`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    name,
                    email,
                    phone: phone || null,
                    message: message || null,
                    website_trap: websiteTrap,
                }),
            });
            if (res.ok) {
                setSubmitted(true);
            } else {
                const data = await res.json().catch(() => ({}));
                setError(data.message || 'Une erreur est survenue. Veuillez réessayer.');
            }
        } catch (err) {
            setError('Erreur de connexion. Veuillez réessayer.');
        } finally {
            setLoading(false);
        }
    };

    if (submitted) {
        return (
            <div className="p-5 rounded-3xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-center space-y-2">
                <CheckCircle2 className="w-8 h-8 text-emerald-500 mx-auto" />
                <h4 className="text-sm font-bold text-emerald-900 dark:text-emerald-200">Message envoyé !</h4>
                <p className="text-xs text-emerald-700 dark:text-emerald-300">
                    Merci pour votre message. Nous vous répondrons dans les plus brefs délais.
                </p>
            </div>
        );
    }

    return (
        <form onSubmit={handleSubmit} className="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
            <div className="flex items-center gap-2">
                <Inbox className="w-4 h-4 text-indigo-500" />
                <h3 className="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    {content.headline || title || 'Envoyer un message'}
                </h3>
            </div>
            {content.description && <p className="text-xs text-slate-500">{content.description}</p>}

            {/* Honeypot field for anti-spam bots */}
            <input
                type="text"
                name="website_trap"
                value={websiteTrap}
                onChange={(e) => setWebsiteTrap(e.target.value)}
                style={{ display: 'none' }}
                tabIndex={-1}
                autoComplete="off"
            />

            <div className="space-y-2 text-xs">
                <input
                    type="text"
                    required
                    placeholder="Votre Nom *"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <input
                    type="email"
                    required
                    placeholder="Votre Email *"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <input
                    type="tel"
                    placeholder="Numéro de téléphone (optionnel)"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <textarea
                    rows={2}
                    placeholder="Votre message..."
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                    className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                {error && <p className="text-xs text-rose-500">{error}</p>}
                <button
                    type="submit"
                    disabled={loading}
                    className="w-full py-2.5 rounded-xl text-white font-bold text-xs transition-opacity hover:opacity-90 disabled:opacity-50 cursor-pointer"
                    style={{ backgroundColor: primaryColor }}
                >
                    {loading ? 'Envoi...' : (content.submit_label || 'Envoyer')}
                </button>
            </div>
        </form>
    );
};

