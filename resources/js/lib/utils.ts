import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    const checkUrl = toUrl(urlToCheck);
    if (!checkUrl) {
        return false;
    }

    // Extract pathname from both URLs (ignore query parameters and hash)
    const getPathname = (url: string): string => {
        return url.split('?')[0].split('#')[0];
    };

    return getPathname(checkUrl) === getPathname(currentUrl);
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
